<?php

use App\Http\Controllers\Admin\OrderController;
use App\Models\Cart;

Route::group([
    'controller' => OrderController::class,
    'middleware' => ['auth:admin', 'can:view,'.Cart::class],
    'as' => 'orders.', //naming prefix
    'prefix' => 'orders', //for routes
], function() {
    Route::get('', 'index')->name('index');
    Route::get('xlsx', 'index')->name('index_xlsx')->defaults('xlsx', true);
    Route::post('', 'store')->name('store')->middleware('can:create,'.Cart::class);
    Route::get('{chOrder}/edit', 'edit')->name('edit');
    Route::get('{chOrder}/overview', 'overview')->name('overview');
    Route::get('{chOrder}/move/{direction}', "move")->name('move')->middleware('can:update,chOrder');

    // @HOOK_ROUTES_MODEL

    Route::get('{chOrder}', 'edit')->name('show');
    Route::patch('{chOrder}', 'update')->name('update')->middleware('can:update,chOrder');
    Route::delete('{chOrder}', 'destroy')->name('destroy')->middleware('can:delete,chOrder');

    // @HOOK_ROUTES
});
