<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcategoria extends Model
{
    use SoftDeletes;
    protected $table = 'subcategoria';
    protected $primaryKey = 'ID_sub';
    protected $fillable = ['ID_cat', 'nombre_subcategoria'];
    
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'ID_cat', 'idCat');
    }
    
    public function subcategoria2()
    {
        return $this->hasMany(Subcategoriax2::class, 'ID_sub', 'ID_sub');
    }
}

