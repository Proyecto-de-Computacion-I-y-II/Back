<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Model
{
    use SoftDeletes;
    
    protected $table = 'usuario';
    protected $primaryKey = 'ID_user';
    protected $fillable = ['nombre', 'apellidos', 'correo', 'contrasenia','rol'];
    
    public function cestas()
    {
        return $this->hasMany(Cesta_Compra::class, 'ID_user', 'ID_user');
    }
}