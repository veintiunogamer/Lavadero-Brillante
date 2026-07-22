<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';
    protected $table = 'invoice';

    protected $fillable = [
        'id',
        'order_id',
        'business_name',
        'nif',
        'email',
        'phone',
        'address',
        'city',
        'zipcode',
        'creation_date'
    ];

    /**
     * Relación inversa con Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
