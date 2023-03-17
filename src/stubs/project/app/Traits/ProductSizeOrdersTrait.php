<?php
namespace App\Traits;

use App\Models\CartProduct;

/**
 * @see \App\Interfaces\CartProductable
 */
trait ProductSizeOrdersTrait {

    public function cart_products() {
        return $this->morphMany( CartProduct::class, 'owner');
    }

    public function checkQuantity($value) {
        if(config('app.MINUS_QUANTITIES'))
            return true;
        return $this->quantity >= $value;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function getUsableQuantityAttribute() {
        return $this->getQuantity() + $this->getNotConfirmedCartsQuantity();
    }

    public function getNotConfirmedCartsQuantity($user = null) {
        $qry = $this->cart_products()
            ->whereHas('cart', function($qry2) use ($user) {
                $qry2->whereNull('status')
                    ->whereNull('confirmed_at')
                    ->whereNull('processing_from');
                if($user) $qry2->where("user_id", $user->id);
            });

        return $qry->sum('quantity');
    }

    public function setQuantity($newQuantity) {
        $this->update([
            'quantity' => $newQuantity
        ]);
    }

    public function getVat_In_Price() {
        return (boolean)config('app.VAT_IN_PRICE');
    }

    public function isInOrder($order) {
        return CartProduct::where([
            'cart_id' => $order->id,
            'owner_type' => get_class($this),
            'owner_id' => $this->id
        ])->first();
    }

    public function getCartProductName() {
        $sizeName = $this->aVar('name');
        if($sizeName == '#') return $this->product->aVar('name');
        return $this->product->aVar('name').'-'.$sizeName;
    }

    public function getReference($cartProduct) {
        return 'size_'.$this->id;
    }

}
