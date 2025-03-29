<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto_temp extends Model
{    
    use SoftDeletes;
    protected $table = 'productos_temp';
    protected $primaryKey = 'idTemp';
    protected $fillable = ['producto', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    
    public function productos()
    {
        return $this->hasMany(Producto::class, 'idTemp', 'idTemp');
    }
}
