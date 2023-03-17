<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();;
            $table->unsignedSmallInteger('site_id');
            $table->string('session_id')->index();
            $table->string('guard')->index();
            $table->unsignedBigInteger('user_id')->nullable();
//            $table->float('vat')->default(0);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processing_from')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
        Schema::create('cart_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cart_id')->index();
            $table->unsignedBigInteger('owner_id');
            $table->string('owner_type');
            $table->float('quantity')->default(0);
            $table->float('price')->default(0);
            $table->float('real_price')->default(0);
            $table->boolean('use_reprice')->default(true);
            $table->float('vat')->default(0);
            $table->boolean('vat_in_price')->default(0);
            $table->integer('ord')->default(0);
            $table->timestamps();

            $table->index(['owner_id', 'owner_type']);
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
        });
        Schema::create('cart_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cart_id')->index();
            $table->unsignedBigInteger('delivery_method_id')->index();
            $table->string('type');
            $table->float('tax')->default(0);
            $table->float('real_tax')->default(0);
            $table->float('vat')->default(0);
            $table->boolean('vat_in_delivery')->default(0);
            $table->string('overview')->nullable();
            $table->timestamps();

            $table->unique([
                'cart_id', 'delivery_method_id'
            ], 'cart_delivery');
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
        });
        Schema::create('cart_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cart_id')->index();
            $table->unsignedBigInteger('payment_method_id')->index();
            $table->string('type');
            $table->float('tax')->default(0);
            $table->float('real_tax')->default(0);
            $table->float('vat')->default(0);
            $table->boolean('vat_in_payment')->default(0);
            $table->string('overview')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->unique([
                'cart_id', 'payment_method_id'
            ], 'cart_payment');
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_payments');
        Schema::dropIfExists('cart_deliveries');
        Schema::dropIfExists('cart_products');
        Schema::dropIfExists('carts');
    }
};
