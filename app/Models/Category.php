<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $incrementing = false;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $keyType = 'string';
    protected $table = 'category';

    protected $fillable = [
        'id', 
        'cat_name', 
        'status', 
        'creation_date'
    ];
}
