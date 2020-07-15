<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    /**
     * @var string[] 
     */
    protected $attributes = [
        'order_status_id' => OrderStatus::ID_NEW
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}