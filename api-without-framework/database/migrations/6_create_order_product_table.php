<?php

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

class CreateOrderProductTable
{
    public function up()
    {
        Manager::schema()->create('order_product', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');

            $table->index('product_id');

            $table->primary([
                'order_id',
                'product_id',
            ]);

            $table->foreign('order_id')->on('orders')->references('id')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('product_id')->on('products')->references('id')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });
    }
}