<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';
    protected $table = 'client';

    protected $fillable = [
        'id',
        'name',
        'phone',
        'license_plaque',
        'status',
        'creation_date'
    ];

}
