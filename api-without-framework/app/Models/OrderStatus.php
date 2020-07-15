<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    const ID_NEW = 'new';

    const ID_PAID = 'paid';

    protected $keyType = 'string';
}