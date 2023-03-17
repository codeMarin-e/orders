<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use App\Models\Discount;
use App\Models\DeliveryMethod;
use App\Models\Cart;
use App\Traits\MacroableModel;
use App\Traits\Discountable;
use App\Traits\AddVariable;

class CartDelivery extends Model
{
    protected $fillable = ['cart_id', 'delivery_method_id', 'tax', 'real_tax', 'vat', 'type', 'vat_in_delivery', 'overview'];
    protected $touches = [ 'cart' ];

    use MacroableModel;
    use Discountable;
    use AddVariable;

    // @HOOK_TRAITS

    protected static function boot() {
        parent::boot();
        static::deleting( static::class.'@onDeleting_event' );
        static::deleted( static::class.'@onDeleted_event' );

        // @HOOK_BOOT
    }

    public function cart() {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    public function delivery() {
        return $this->belongsTo(DeliveryMethod::class, 'delivery_method_id', 'id');
    }

    public function refreshDiscounts() {
        $this->loadMissing('delivery');
        if(!$this->delivery) return;
        // @HOOK_refreshDiscounts_START
        if(!method_exists(DeliveryMethod::class, 'getDiscounts') && !DeliveryMethod::hasMacro('getDiscounts')) return;
        $this->onDeleting_discounts($this); //delete discounts to put new
        foreach($this->delivery->activeDiscounts() as $discount) {
            $this->addDiscount(Arr::except($discount->getAttributes(), Discount::mergeExceptFields()));
        }
        // @HOOK_refreshDiscounts_END
    }

    public function rePrice() {
        event( 'cart.rePrice.delivery.start', [$this] );
        // @HOOK_rePrice_START
        $this->loadMissing('delivery');
        if(!$this->delivery) return;
        $this->update([
            'tax' => (float)$this->delivery->getTax(true, true, (bool)config('app.VAT_IN_DELIVERY')),
            'real_tax' => (float)$this->delivery->getTax(false, config('marinar_orders.set_delivery_tax_with_discount'), (bool)config('app.VAT_IN_DELIVERY')),
            'vat' => (float)$this->delivery->getVatPercent(),
            'vat_in_delivery' => (bool)config('app.VAT_IN_DELIVERY'),
        ]);
        $this->setAvar('name', $this->delivery->aVar('name'));

        $this->refreshDiscounts();
        $this->refresh();
        // @HOOK_rePrice_END
        event( 'cart.rePrice.delivery.end', [$this] );
    }

    public function reCancel() {
        event( 'cart.reCancel.delivery', [$this] );
    }
    public function cancel() {
        event( 'cart.cancel.delivery', [$this] );
    }

    public function getDiscountValue($tax = null) {
        $tax = is_numeric($tax)? $tax : $this->tax;
        //TO DO
        $return = 0;
        foreach($this->discounts as $discount) {
            //may put your logic here
            $return += $discount->getValue($tax);
        }
        return $return;
    }

    public function getVatPercent() {
        return $this->vat;
    }

    public function getVat($tax = null) {
        $tax = is_numeric($tax)? $tax : $this->getTax(true, $this->vat_in_delivery);
        $vatPercent = $this->getVatPercent();
        if($this->vat_in_delivery) {
            $vat = $tax - ($tax / (1 + ($vatPercent / 100)));
        } else {
            $vat = $tax*($vatPercent/100);
        }
        return $vat;
    }

    public function getTax($withDiscount = true, $withVat = true) {
        // @HOOK_getTax_START
        $tax = $this->tax;
        if($withDiscount) {
            $tax -= $this->getDiscountValue($tax);
        }
        if($tax < 0) return 0;

        if($withVat) {
            if(!$this->vat_in_delivery) {
                $tax += $this->getVat($tax);
            }
        } else {
            if($this->vat_in_delivery) {
                $tax -= $this->getVat($tax);
            }
        }
        // @HOOK_getTax_END
        return $tax;
    }
    public static function onDeleting_event($model) {
        event( 'cart.changing.delivery.removing', [$model] );
    }

    public static function onDeleted_event($model) {
        event( 'cart.changed.delivery.removed', [$model] );
    }

}
