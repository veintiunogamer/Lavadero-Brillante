<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    const ACTIVE = 1;
    const INACTIVE = 0;

    protected $keyType = 'string';
    protected $table = 'taxes';

    protected $fillable = [
        'id',
        'percent',
        'status',
        'creation_date'
    ];
}
