<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    public const STATUS_DISABLED = 0;
    public const STATUS_ENABLED = 1;

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
        'creation_date',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    /**
     * Relación inversa con Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
