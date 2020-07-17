<?php

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable
{
    public function up()
    {
        Manager::schema()->create('orders', function (Blueprint $table) {
            $table->id();

            $table->decimal('sum');
            $table->integer('user_id');
            $table->string('order_status_id');

            $table->index('order_status_id');
            $table->index('user_id');

            $table->foreign('order_status_id')->on('order_statuses')->references('id')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('user_id')->on('users')->references('id')->onDelete('RESTRICT')->onUpdate('CASCADE');

            $table->timestamps();
        });
    }
}