<?php
namespace App\Traits;

use App\Models\Cart;

trait UserOrdersTrait {

    public static function bootUserOrdersTrait() {
        static::deleting( static::class.'@onDeleting_orders' );
    }

    public function orders() {
        return $this->hasMany(Cart::class, 'user_id', 'id')->where('confirmed_at', '!=', null);
    }

    public function onDeleting_orders($model) {
        $model->loadMissing('orders');
        foreach($model->orders as $order) { //not mass update - to can use update events
            $order->update([
                'user_id' => null,
            ]);
        }
    }
}
