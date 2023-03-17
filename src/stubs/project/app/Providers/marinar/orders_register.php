<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Policies\CartPolicy;

//CURRENT CART
App::singleton('Cart', function(){
    return Cart::current();
});

Route::model('chOrder', Cart::class);
Route::model('chCartProduct', CartProduct::class);
Gate::policy(Cart::class, CartPolicy::class);
