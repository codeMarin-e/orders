<?php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Arr;
    use App\Models\Discount;
    use App\Models\Cart;
    use App\Models\PaymentMethod;
    use App\Traits\MacroableModel;
    use App\Traits\Discountable;
    use App\Traits\AddVariable;

    class CartPayment extends Model
    {
        protected $fillable = ['cart_id', 'payment_method_id', 'tax', 'real_tax', 'vat', 'type', 'vat_in_payment', 'overview'];
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

        public function payment() {
            return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
        }

        public function refreshDiscounts() {
            $this->loadMissing('payment');
            if(!$this->payment) return;
            // @HOOK_refreshDiscounts_START
            if(!method_exists(PaymentMethod::class, 'getDiscounts') && !PaymentMethod::hasMacro('getDiscounts')) return;
            $this->onDeleting_discounts($this); //delete discounts to put new
            foreach($this->payment->activeDiscounts() as $discount) {
                $this->addDiscount(Arr::except($discount->getAttributes(), Discount::mergeExceptFields()));
            }
            // @HOOK_refreshDiscounts_END
        }

        public function rePrice() {
            event( 'cart.rePrice.payment.start', [$this] );
            $this->loadMissing('payment');
            if(!$this->payment) return;
            // @HOOK_rePrice_START
            $this->update([
                    'tax' => (float)$this->payment->getTax(true, true, (bool)config('app.VAT_IN_PAYMENT')),
                    'real_tax' => (float)$this->payment->getTax(false, config('marinar_orders.set_payment_tax_with_discount'), (bool)config('app.VAT_IN_PAYMENT')),
                    'vat' => (float)$this->payment->getVatPercent(),
                    'vat_in_payment' => (bool)config('app.VAT_IN_PAYMENT'),
                ]);
            $this->setAvar('name', $this->payment->aVar('name'));
            $this->refreshDiscounts();
            $this->refresh();
            // @HOOK_rePrice_END
            event( 'cart.rePrice.payment.end', [$this] );
        }

        public function reCancel() {
            event( 'cart.reCancel.payment', [$this] );
        }
        public function cancel() {
            event( 'cart.cancel.payment', [$this] );
        }

        public function getDiscountValue($tax = null) {
            $tax = is_numeric($tax)? $tax : $this->tax;
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
            $tax = is_numeric($tax)? $tax : $this->getTax(true, $this->vat_in_payment);
            $vatPercent = $this->getVatPercent();
            if($this->vat_in_payment) {
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
                if(!$this->vat_in_payment) {
                    $tax += $this->getVat($tax);
                }
            } else {
                if($this->vat_in_payment) {
                    $tax -= $this->getVat($tax);
                }
            }
            // @HOOK_getTax_END
            return $tax;
        }

        public static function onDeleting_event($model) {
            event( 'cart.changing.payment.removing', [$model] );
        }

        public static function onDeleted_event($model) {
            event( 'cart.changed.payment.removed', [$model] );
        }

    }
