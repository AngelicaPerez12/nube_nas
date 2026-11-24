<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $fillable = ['nombre', 'carpeta'];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
