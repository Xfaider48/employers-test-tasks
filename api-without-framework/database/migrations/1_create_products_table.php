<?php

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsTable
{
    public function up()
    {
        Manager::schema()->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price');

            $table->timestamps();
        });
    }
}