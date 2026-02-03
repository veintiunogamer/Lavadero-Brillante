<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Tipos de usuarios - Roles
    const USER_ADMIN = 1;
    const USER_OPERATOR = 2;
    const USER_CASHIER = 3;

    // Estado del usuario
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * Indica que el ID no es auto-incremental
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * El tipo de dato del ID
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'username',
        'password',
        'rol',
        'status',
        'creation_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'creation_date' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'rol', 'id');
    }

    /**
     * Verifica si el usuario es administrador
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role && $this->role->type == Role::TYPE_ADMIN;
    }
}
