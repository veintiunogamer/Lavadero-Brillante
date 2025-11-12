<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';
    protected $table = 'roles';

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    const TYPE_ADMIN = 1;
    const TYPE_USER = 2;

    protected $fillable = [
        'id',
        'name',
        'type',
        'status',
        'creation_date'
    ];
}
