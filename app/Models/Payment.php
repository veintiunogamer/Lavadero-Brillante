<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    // Estados de pago
    const STATUS_PENDING = 1;
    const STATUS_PARTIAL = 2;
    const STATUS_PAID = 3;

    // Tipos de pago
    const TYPE_CASH = 1;
    const TYPE_CARD = 2;
    const TYPE_TRANSFER = 3;

    protected $keyType = 'string';
    protected $table = 'payments';

    protected $fillable = [
        'id',
        'order_id', 
        'type',
        'subtotal', 
        'total', 
        'status', 
        'creation_date'
    ];

    /**
     * Relación con Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

}
