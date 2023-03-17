<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use App\Models\Discount;
use App\Models\Cart;
use App\Traits\Discountable;
use App\Traits\MacroableModel;
use App\Traits\Orderable;
use App\Traits\AddVariable;
use App\Exceptions\CartException;
//use App\Contracts\CartProductable;

class CartProduct extends Model
{
    protected $fillable = ['cart_id', 'real_price', 'price', 'use_reprice', 'quantity', 'vat', 'ord', 'owner_id', 'owner_type', 'vat_in_price'];
    protected $touches = [ 'cart' ];

    use Discountable;
    use MacroableModel;
    use AddVariable;

    //ORDERABLE
    use Orderable;
    public function orderableQryBld($qryBld = null) {
        $qryBld = $qryBld? clone $qryBld : $this;
        return $qryBld->where([
            [ 'cart_id', $this->cart_id ],
        ]);
    }
    //END ORDERABLE

    // @HOOK_TRAITS

    protected static function boot() {
        parent::boot();
        static::deleting( static::class.'@onDeleting_owner' );
        static::deleting( static::class.'@onDeleting_event' );
        static::deleted( static::class.'@onDeleted_event' );

        // @HOOK_BOOT
    }

    public function cart() {
        return $this->belongsTo( Cart::class, 'cart_id');
    }

    public function owner() {
        return $this->morphTo();
    }

    //@see Marinar\Orders\Contracts\CartProductable for $owner
    public function setOwner($owner) {
        $this->loadMissing('owner');
        if(($oldOwner = $this->owner) && $oldOwner->is($owner)) return;
        // @HOOK_setOwner_START
        event( 'cart.changing.product.setting_owner', [$this, $owner] );
        $quantity = $this->quantity;
        if(!config('app.MINUS_QUANTITIES')) {
            if(!($ownerQuantity = $owner->getQuantity()))
                throw new CartException( trans('admin/orders/exceptions.owner_not_enough_quantity'), 500);
            if($ownerQuantity < $quantity)
                $quantity = 1;
            $oldOwner->setQuantity( $oldOwner->getQuantity() + $this->quantity);
            $owner->setQuantity( $ownerQuantity - $quantity );
        }
        $this->update([
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
            'quantity' => $quantity,
        ]);
        $this->load('owner');
        // @HOOK_setOwner_END
        event( 'cart.changed.product.set_owner', [$this, $oldOwner] );
    }

    public function checkQuantity($quantity) {
        // @HOOK_checkQuantity_START
        $oldQuantity = $this->quantity;
        $deltaQuantity = $quantity - $oldQuantity;
        $this->loadMissing('owner');
        if(!($owner = $this->owner)) return;
        if(!config('app.MINUS_QUANTITIES')) {
            if ($owner->getQuantity() < $deltaQuantity)
                return false;
        }
        // @HOOK_checkQuantity_START
        return true;
    }

    public function setQuantity($quantity) {
        if($this->quantity == $quantity) return;
        // @HOOK_setQuantity_START
        $oldQuantity = $this->quantity;
        $deltaQuantity = $quantity - $oldQuantity;
        if(!($owner = $this->owner)) {
            throw new CartException(trans('admin/orders/exceptions.not_set_owner'), 500);
        }
        if(!$this->checkQuantity($quantity)) {
            throw new CartException(trans('admin/orders/exceptions.owner_not_enough_quantity'), 500);
        }
        if(!$quantity) {
            $this->delete();
            return;
        }
        event( 'cart.changing.product.setting_quantity', [$this, $quantity] );
        $owner->setQuantity($owner->getQuantity() - $deltaQuantity);
        $this->update([
            'quantity' => $quantity
        ]);
        // @HOOK_setQuantity_END
        event( 'cart.changed.product.set_quantity', [$this, $oldQuantity] );
    }

    public static function onDeleting_owner($model) {
        $model->loadMissing('owner', 'cart');
        if($model->cart->status == 'canceled') return;
        if(!($owner = $model->owner)) return;
        $leftQuantity =  $owner->getQuantity();
        if(is_numeric($leftQuantity))
            $owner->setQuantity( $leftQuantity + $model->quantity );
    }

    public function refreshDiscounts() {
        $this->loadMissing('owner');
        // @HOOK_refreshDiscounts_START
        if(!$this->owner) return;
        $ownerClass = $this->owner_type;
        if(!method_exists($ownerClass, 'getDiscounts') && !$ownerClass::hasMacro('getDiscounts'))
            return;
        $this->onDeleting_discounts($this); //delete discounts to put new
        foreach($this->owner->activeDiscounts() as $discount) {
            $this->addDiscount(Arr::except($discount->getAttributes(), Discount::mergeExceptFields()));
        }
        // @HOOK_refreshDiscounts_END
    }

    public function rePrice() {
        event( 'cart.rePrice.product.start', [$this] );
        // @HOOK_rePrice_START
        if(!($owner = $this->owner()->first())) return;
        $updates = [
            'real_price' => (float)$owner->getPrice(false, config('marinar_orders.ADD_SIZE_REAL_PRICE_WITH_DISCOUNT'), (bool)$owner->getVat_In_Price()),
            'vat' => (float)$owner->getVatPercent(),
            'vat_in_price' => (bool)$owner->getVat_In_Price(),
        ];
        if($this->use_reprice) {
            $updates['price'] = (float)$owner->getPrice(true, false, (bool)$owner->getVat_In_Price());
        }
        $this->update($updates);
        $this->refreshDiscounts();
        $this->refresh();
        // @HOOK_rePrice_END
        event( 'cart.rePrice.product.end', [$this] );
    }

    public function reCancel() {
        event( 'cart.reCancel.product.start', [$this] );
        // @HOOK_reCancel_START
        $deltaQuantity = $this->quantity;
        if(!($owner = $this->owner)) {
            throw new CartException(trans('admin/orders/exceptions.not_set_owner'), 500);
        }
        if(!$this->checkQuantity($this->quantity*2)) { //it removes from now quantity
            throw new CartException(trans('admin/orders/exceptions.owner_not_enough_quantity'), 500);
        }
        $owner->setQuantity($owner->getQuantity() - $deltaQuantity);
        // @HOOK_reCancel_END
        event( 'cart.reCancel.product.end', [$this] );
    }
    public function cancel() {
        event( 'cart.cancel.product.start', [$this] );
        static::onDeleting_owner($this);
        event( 'cart.cancel.product.end', [$this] );
    }

    public function getDiscountValue($price = null) {
        $price = is_numeric($price)? $price : $this->price;
        $return = 0;
        foreach($this->discounts as $discount) {
            //may put your logic here
            $return += $discount->getValue($price);
        }
        return $return;
    }

    public function getVatPercent() {
        return $this->vat;
    }

    public function getVat($price = null) {
        $price = is_numeric($price)? $price : $this->getPrice(true, $this->vat_in_price);
        $vatPercent = $this->getVatPercent();
        if($this->vat_in_price) {
            $vat = $price - ($price / (1 + ($vatPercent / 100)));
        } else {
            $vat = $price*($vatPercent/100);
        }
        return $vat;
    }

    public function getPrice($withDiscount = true, $withVat = true) {
        // @HOOK_getPrice_START
        $price = $this->price;
        if($withDiscount) {
            $price -= $this->getDiscountValue($price);
        }
        if($price < 0) return 0;
        if($withVat) {
            if(!$this->vat_in_price) {
                $price += $this->getVat($price);
            }
        } else {
            if($this->vat_in_price) {
                $price -= $this->getVat($price);
            }
        }
        // @HOOK_getPrice_END
        return $price;
    }

    public function getTotalPrice($withDiscount = true, $withVat = true) {
        // @HOOK_getTotalPrice
        return $this->getPrice($withDiscount, $withVat) * $this->quantity;
    }

    public static function onDeleting_event($model) {
        event( 'cart.changing.product.removing', [$model] );
    }

    public static function onDeleted_event($model) {
        event( 'cart.changed.product.removed', [$model] );
    }
}
