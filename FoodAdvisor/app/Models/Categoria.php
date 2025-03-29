<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use SoftDeletes;
    protected $table = 'categoria';
    protected $primaryKey = 'idCat';
    protected $fillable = ['nombre_categoria'];
    
    public function subcategorias()
    {
        return $this->hasMany(Subcategoria::class, 'ID_cat', 'idCat');
    }
}
