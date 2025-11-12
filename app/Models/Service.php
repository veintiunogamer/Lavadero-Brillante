<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';
    protected $table = 'services';

    protected $fillable = [
        'id', 
        'category_id', 
        'name',
        'details', 
        'value', 
        'duration', 
        'creation_date'
    ];
    
}
