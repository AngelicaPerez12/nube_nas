<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'grupo_id',
        'permiso',
    ];

    /**
     * Atributos ocultos al serializar.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión automática de tipos de datos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Relación: un usuario pertenece a un grupo (departamento).
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }
}