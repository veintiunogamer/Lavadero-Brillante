<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $incrementing = false;

    const STATUS_PENDING = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELED = 4;

    const DIRTY_LEVEL_LOW = 1;
    const DIRTY_LEVEL_MEDIUM = 2;
    const DIRTY_LEVEL_HIGH = 3;
    
    protected $keyType = 'string';
    protected $table = 'order';

    protected $fillable = [
        'id', 
        'client_id',
        'user_id', 
        'service_id', 
        'quantity', 
        'dirt_level', 
        'hour_in', 
        'hour_out', 
        'vehicle_notes', 
        'discount', 
        'subtotal', 
        'taxes', 
        'total', 
        'order_notes', 
        'extra_notes', 
        'status', 
        'creation_date'
    ];

    /**
     * Relación con Cliente
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    /**
     * Relación con Servicio
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    /**
     * Relación con Usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
