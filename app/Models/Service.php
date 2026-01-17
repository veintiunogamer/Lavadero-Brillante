<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';
    protected $table = 'services';

    protected $fillable = [
        'id', 
        'category_id', 
        'name',
        'details', 
        'value', 
        'duration',
        'status',
        'creation_date'
    ];

    /**
     * Relación muchos a muchos con Orders a través de la tabla intermedia order_services
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_services', 'service_id', 'order_id')
                    ->withPivot('id', 'subtotal', 'total', 'created_at')
                    ->using(OrderService::class);
    }

    /**
     * Relación con OrderServices (tabla intermedia)
     */
    public function orderServices()
    {
        return $this->hasMany(OrderService::class, 'service_id', 'id');
    }
}
