<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderService extends Pivot
{
    public $incrementing = false;

    protected $keyType = 'string';
    protected $table = 'order_services';

    protected $fillable = [
        'id',
        'service_id',
        'order_id',
        'subtotal',
        'total',
        'created_at'
    ];

    /**
     * Indica si el modelo debe tener timestamps automáticos (updated_at)
     */
    const UPDATED_AT = null;

    /**
     * Relación con Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Relación con Service
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
}
