<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grupo extends Model


{
    protected $table = 'grupos';
    
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion', 'created_by'];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'grupo_user', 'grupo_id', 'user_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
