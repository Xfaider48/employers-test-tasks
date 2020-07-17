<?php

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

class CreateOrderStatusesTable
{
    public function up()
    {
        Manager::schema()->create('order_statuses', function (Blueprint $table) {
            $table->string('id');
            $table->primary('id');

            $table->timestamps();
        });
    }
}