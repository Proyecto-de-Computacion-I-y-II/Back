<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NivelPiramide extends Model
{
    use SoftDeletes;
    
    protected $table = 'nivel_piramide';
    protected $primaryKey = 'idNivel';
    protected $fillable = ['Nombre'];
    
    public function productos()
    {
        return $this->hasMany(Producto::class, 'idNivel', 'idNivel');
    }
}

