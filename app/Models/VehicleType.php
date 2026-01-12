<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class VehicleType extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';
    protected $table = 'vehicle_type';

    protected $fillable = [
        'id',
        'name',
        'status',
        'creation_date'
    ];
}
