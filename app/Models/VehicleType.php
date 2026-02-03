<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class VehicleType extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';
    protected $table = 'vehicle_type';

    // Estados de tipo de vehículo
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'id',
        'name',
        'status',
        'creation_date'
    ];
}
