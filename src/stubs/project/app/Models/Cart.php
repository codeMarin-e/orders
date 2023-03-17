<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Discount;
use App\Models\DeliveryMethod;
use App\Models\PaymentMethod;
use App\Models\CartDelivery;
use App\Models\CartPayment;
use App\Models\CartProduct;

use App\Traits\MacroableModel;
use App\Traits\Addressable;
use App\Traits\Discountable;
use App\Traits\AddVariable;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Cart extends Model {
    protected $fillable = ['order_id', 'session_id','guard', 'site_id', 'user_id', 'vat', 'confirmed_at', 'processed_at', 'status', 'reserved_at'];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'processing_from' => 'datetime',
    ];

    public static $statuses = [
        'new' => 'admin/orders/orders.statuses.new',
        'processing' => 'admin/orders/orders.statuses.processing',
        'reserved' => 'admin/orders/orders.statuses.reserved',
        'canceled' => 'admin/orders/orders.statuses.canceled',
//        'confirmed' => 'admin/orders/orders.statuses.confirmed',
    ];

    use MacroableModel;
    use Addressable;
    use AddVariable;
    use Discountable;

    // @HOOK_TRAITS

    public function getFacturaAddress() {
        return $this->getAddress(type: 'factura');
    }

    public function getDeliveryAddress() {
        return $this->getAddress(type: 'delivery');
    }

    public function freeOrderId($qryBld = null) {
        return $this->id;
        $qryBld = $qryBld? clone $qryBld : static::getModel();
        return (int)$qryBld->select(DB::raw("MAX(order_id) as freeOrderId")->getValue(DB::connection()->getQueryGrammar()))
                ->where('status', '!=', null)->first()->freeOrderId+1;
    }

    public function products() {
        return $this->hasMany( CartProduct::class, 'cart_id', 'id');
    }

    public function delivery() {
        return $this->hasOne(CartDelivery::class, 'cart_id', 'id');
    }

    public function payment() {
        return $this->hasOne(CartPayment::class, 'cart_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected static function boot() {
        parent::boot();
        static::deleting( static::class.'@onDeleting_clear' );

        // @HOOK_BOOT
    }

    public static function current($guard = null, $attributes = []) {
        $guard = $guard === null? config('auth.defaults.guard') : (string)$guard;
        // @HOOK_CURRENT
        return static::firstOrCreate(array_merge($attributes, [
            'site_id' => app()->make('Site')->id,
            'session_id' => (isset($attributes['session_id']))?
                ($attributes['session_id'] === false? null :  $attributes['session_id'] ):
                session()->getId(),
            'guard' => $guard,
            'user_id' => auth($guard)->user()?->id,
            'confirmed_at' => null,
            'status' => null,
        ]));
    }

    public function regenerate($attributes = []) {
        static::where([
            'session_id' => $this->session_id,
            'confirmed_at' => null,
            'status' => null,
        ])->update(array_merge(['session_id' => session()->getId()], $attributes));
    }

    //@see App\Contracts\CartProductable for $owner
    public function addProduct( $owner, $otherAttr = [] ) {
        event( 'cart.changing.adding_product', [$this, $owner] );
        // @HOOK_addProduct_START

        $getQuantity = isset($otherAttr['quantity'])? (float)$otherAttr['quantity'] : 1;
        if(!$owner->checkQuantity($getQuantity)) return false;

        $owner->setQuantity( $owner->getQuantity()-$getQuantity );

        $otherAttr = array_merge([
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
            'real_price' => (float)$owner->getPrice(false, config('marinar_orders.add_owner_real_price_with_discount'), (bool)config('app.VAT_IN_PRICE')),
            'price' => (float)$owner->getPrice(true, false, (bool)config('app.VAT_IN_PRICE')),
            'vat' => (float)$owner->getVatPercent(),
            'vat_in_price' => (bool)config('app.VAT_IN_PRICE'),
            'quantity' => $getQuantity,
        ], $otherAttr);

        if($cartProduct = $this->products()->where([
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
        ])->first()) {
            $otherAttr['quantity'] += $cartProduct->quantity;
            $cartProduct->update($otherAttr);
        } else {
            $cartProduct = $this->products()->create($otherAttr);
        }

        // @HOOK_addProduct_END
        event( 'cart.changed.added_product', [$this, $cartProduct] );
        return $cartProduct;
    }

    public function getRealDelivery() {
        $this->loadMissing('delivery.delivery');
        return $this->deivery?->delivery;
    }

    public function setDelivery( DeliveryMethod $delivery, $otherAttr = []) {
        $this->loadMissing('delivery');
        if(($oldDelivery = $this->getRealDelivery() ) && $oldDelivery->is($delivery)) return;
        // @HOOK_setDelivery_START
        event( 'cart.changing.setting_delivery', [$this, $delivery] );
        $cartDelivery = $this->delivery()->updateOrCreate([
            'cart_id' => $this->id,
        ], array_merge([
            'delivery_method_id' => $delivery->id,
            'type' => $delivery->type,
            'tax' => $delivery->getTax(true, true, (bool)config('app.VAT_IN_DELIVERY')),
            'real_tax' => $delivery->getTax(false, config('marinar_orders.set_delivery_tax_with_discount'), (bool)config('app.VAT_IN_DELIVERY')),
            'vat' => $delivery->getVatPercent(),
            'vat_in_delivery' => (bool)config('app.VAT_IN_DELIVERY'),
            'overview' => $delivery->overview,
        ], $otherAttr));
        $cartDelivery->setAvar('name', $delivery->aVar('name'));
        $this->load('delivery');

        // @HOOK_setDelivery_END
        event( 'cart.changed.set_delivery', [$this, $oldDelivery] );
        return $cartDelivery;
    }

    public function getRealPayment() {
        $this->loadMissing('payment.payment');
        return $this->payment?->payment;
    }

    public function setPayment( PaymentMethod $payment, $otherAttr = []) {
        $this->loadMissing('payment');
        if(($oldPayment = $this->getRealPayment()) && $oldPayment->is($payment)) return;
        // @HOOK_setPayment_START
        event( 'cart.changing.setting_payment', [$this, $payment] );
        $cartPayment= $this->payment()->updateOrCreate([
            'cart_id' => $this->id,
        ], array_merge([
            'payment_method_id' => $payment->id,
            'type' => $payment->type,
            'tax' => $payment->getTax(true, true, (bool)config('app.VAT_IN_PAYMENT')),
            'real_tax' => $payment->getTax(false, config('marinar_orders.set_payment_tax_with_discount'), (bool)config('app.VAT_IN_PAYMENT')),
            'vat' => $payment->getVatPercent(),
            'vat_in_payment' => (bool)config('app.VAT_IN_PAYMENT'),
            'overview' => $payment->overview,
        ], $otherAttr));
        $cartPayment->setAvar('name', $payment->aVar('name'));
        $this->load('payment');

        // @HOOK_setPayment_END
        event( 'cart.changed.set_payment', [$this, $oldPayment] );
        return $cartPayment;
    }

    public function refreshDiscounts() {
        // @HOOK_refreshDiscounts_START
        $this->onDeleting_discounts($this); //delete discounts to put new
        foreach($this->activeDiscounts() as $discount) {
            $this->addDiscount(Arr::except($discount->getAttributes(), Discount::mergeExceptFields()));
        }
        // @HOOK_refreshDiscounts_END
    }

    public function rePrice() {
        if($this->confirmed_at) return;
        event( 'cart.rePrice.start', [$this] );

        // @HOOK_REPRICE_START

        $this->loadMissing('products', 'delivery', 'payment');
        foreach($this->products as $cartProduct) {
            $cartProduct->rePrice();
        }
        $this->delivery?->rePrice();
        $this->payment?->rePrice();
        $this->refreshDiscounts();

        // @HOOK_REPRICE_END

        $this->refresh();
        event( 'cart.rePrice.finished', [$this] );
    }

    public function onDeleting_clear($model) {
        $model->loadMissing('products', 'delivery', 'payment');
        foreach($model->products as $cartProduct) {
            $cartProduct->delete();
        }
        $model->delivery?->delete();
        $model->payment?->delete();
        $model->onDeleting_addresses($model);
    }

    public function clear() {
        event( 'cart.clear.start', [$this] );
        $this->onDeleting_clear($this);
        event( 'cart.clear.end', [$this] );
    }

    public function getDeliveryTax($withDiscount = true, $withVat = true) {
        return (float)$this->delivery?->getTax($withDiscount, $withVat);
    }

    public function getPaymentTax($withDiscount = true, $withVat = true) {
        return (float)$this->payment?->getTax($withDiscount, $withVat);
    }

    public function getProductsTotal($withDiscount = true, $withVat = true) {
        $this->loadMissing('products');
        return $this->products->reduce(function($total, $cartProduct) use ($withDiscount, $withVat) {
            return $total+$cartProduct->getTotalPrice($withDiscount, $withVat);
        }, 0);
    }

    public function getTaxesTotal($withDiscount = true, $withVat = true) {
        return $this->getDeliveryTax($withDiscount, $withVat) + $this->getPaymentTax($withDiscount, $withVat);
    }

    public function getDiscountValue($price = null) {
        $price = is_numeric($price)? $price : $this->getTotalPrice(false, false, false);
        $this->loadMissing('discounts');
        return $this->discounts->reduce(function($total, $discount) use ($price) {
            return $total + $discount->getValue($price);
        }, 0);
    }

    public function getDiscountsTotal($total = false) {
        $this->loadMissing('products', 'delivery', 'payment', 'discounts');
        // @HOOK_getDiscountsTotal_START
        $discount = $this->products->reduce(function($total, $cartProduct) {
            return $total + $cartProduct->getDiscountValue();
        }, 0);
        $discount += (float)$this->delivery?->getDiscountValue();
        $discount += (float)$this->payment?->getDiscountValue();
        $discount += $this->getDiscountValue();
        // @HOOK_getDiscountsTotal_END
        return $discount;
    }

    public function getTotalVat($withTaxesVat = true) {
        $this->loadMissing('products', 'delivery', 'payment', 'discounts');
        // @HOOK_getTotalVat_START
        $total = 0;
        $total = $this->products->reduce(function($return, $cartProduct) {
            return $return + ($cartProduct->getVat()*$cartProduct->quantity);
        }, 0);
        if(!$withTaxesVat) return $total;
        $total += (float)$this->delivery?->getVat();
        $total += (float)$this->payment?->getVat();
        // @HOOK_getTotalVat_END
        return $total;
    }

    public function getTotalPrice($withTaxes = true, $withDiscount = true, $withVat = true) {
        // @HOOK_getTotalPrice_START
        $total = 0;
        //total_products_price
        $total += $this->getProductsTotal($withDiscount, $withVat);

        //total_taxes
        if($withTaxes)
        $total += $this->getTaxesTotal($withDiscount, $withVat);

        //total_discount
        if($withDiscount)
        $total -= $this->getDiscountValue($total);

        // @HOOK_getTotalPriceE_END
        return $total;
    }

    public function confirmProducts() {
        $this->loadMissing('products');
        // @HOOK_CONFIRM_PRODUCTS_START
        foreach($this->products as $cartProduct) {
            event( 'cart.confirm.product', [$cartProduct] );
            $cartProduct->setAVar('parts_name', $cartProduct->owner?->getCartProductName());
        }
        // @HOOK_CONFIRM_PRODUCTS_END
    }

    public function confirmDelivery() {
        $this->loadMissing('delivery.delivery');
        if(!$this->delivery) return;
        event( 'cart.confirm.delivery', [$this->delivery] );
        $this->setAVar('name', $this->delivery->delivery->aVar('name'));
        // @HOOK_CONFIRM_DELIVERY
    }

    public function confirmPayment() {
        $this->loadMissing('payment.payment');
        if(!$this->payment) return;
        event( 'cart.confirm.payment', [$this->payment] );
        $this->payment->setAVar('name', $this->payment->payment->aVar('name'));
        // @HOOK_CONFIRM_PAYMENT
    }

    public function confirm(\DateTime $confirmedDT = null, $attributes = []) {
        if($this->confirmed_at) return;
        event( 'cart.confirm.order', [$this] );

        $confirmedDT = $confirmedDT? clone $confirmedDT : new \DateTime();

        // @HOOK_CONFIRM_START

        $this->confirmProducts();
        $this->confirmDelivery();
        $this->confirmPayment();

        $this->setStatus('new', array_merge([
            'confirmed_at' => $confirmedDT,
            'order_id' => $this->freeOrderId(),
        ], $attributes));

        // @HOOK_CONFIRM_END

        event( 'cart.confirmed.order', [$this] );
        foreach($this->products as $cartProduct) {
            event('cart.confirmed.product', [$cartProduct]);
        }
        event( 'cart.confirmed.delivery', [$this->delivery] );
        event( 'cart.confirmed.payment', [$this->payment] );
    }

    public function reCancel() {
        event( 'cart.status.recanceling', [$this] );
        $this->loadMissing('products', 'delivery', 'payment');

        // @HOOK_RECANCEL_START

        foreach($this->products as $cartProduct) {
            $cartProduct->reCancel();
        }
        $this->delivery?->reCancel();
        $this->payment?->reCancel();

        // @HOOK_RECANCEL_END

        event( 'cart.status.recanceled', [$this] );
    }

    public function cancel() {
        event( 'cart.status.canceling', [$this] );
        $this->loadMissing('products', 'delivery', 'payment');

        // @HOOK_CANCEL_START

        foreach($this->products as $cartProduct) {
            $cartProduct->cancel();
        }
        $this->delivery?->cancel();
        $this->payment?->cancel();

        // @HOOK_CANCEL_END

        event( 'cart.status.canceled', [$this] );
    }

    public function setStatus($status, $updateAttributes = []) {
        if(!isset(static::$statuses[$status])) return false;

        // @HOOK_SET_STATUS_START

        $oldStatus = $this->status;
        if($oldStatus == $status) return;
        event( 'cart.status.changing', [$this, $status] );
        $update = ['status' => $status];
        if($oldStatus == 'canceled') {
            $this->reCancel();
        }
        if($status == 'processing') {
            $update['processing_from'] = now();
        } else if($status == 'reserved') {
            $update['reserved_at'] = now();
        } else if($status == 'new') {
            $update['confirmed_at'] = now();
        } else if($status == 'canceled') {
            $this->cancel();
        }
        $this->update(array_merge($update, $updateAttributes));

        // @HOOK_SET_STATUS_END

        event( 'cart.status.changed', [$this, $oldStatus] );
    }
}
