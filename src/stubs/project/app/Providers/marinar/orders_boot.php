<?php

use App\Http\Middleware\SetCurrentCart;
use Illuminate\Support\Facades\Event;
use App\Models\Cart;

app()->make('router')->pushMiddlewareToGroup('web', SetCurrentCart::class);

if(config('marinar_orders.reprice_on_change', true)) {
    Event::listen("cart.changed.*", function ($event_name, $args) {
        $order = $args[0];
        if(!($order instanceof Cart)) {
            $order->loadMissing("cart");
            $order = $order->cart;
        }
        if(!$order->confirmed_at) {
            $order->rePrice();
        }
    });
}

Event::listen("session.regenerate.start", function() {
    session(['regenerate_cart' => app()->make('Cart')->id]);
});

Event::listen("session.regenerate.end", function() {
    $regenerateCart = Cart::find((int)session()->pull('regenerate_cart'));
    $regenerateCart->regenerate(['user_id' => auth()->user()?->id]);
});
