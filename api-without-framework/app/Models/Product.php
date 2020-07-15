<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'price'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'price' => 'decimal:2'
    ];
}