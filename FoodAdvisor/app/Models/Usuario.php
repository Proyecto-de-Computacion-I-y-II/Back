<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable implements JWTSubject
{
    use SoftDeletes, HasApiTokens, Notifiable;
    
    
    protected $table = 'usuario';
    protected $primaryKey = 'ID_user';
    protected $fillable = ['nombre', 'apellidos', 'correo', 'contrasenia','rol'];
    
    public function cestas()
    {
        return $this->hasMany(Cesta_Compra::class, 'ID_user', 'ID_user');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {

        return [];
    }
}