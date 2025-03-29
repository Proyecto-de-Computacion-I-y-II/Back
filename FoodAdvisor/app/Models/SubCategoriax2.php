<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcategoriax2 extends Model
{
    use SoftDeletes;
    protected $table = 'subcategoria2';
    protected $primaryKey = 'ID_sub2';
    protected $fillable = ['ID_sub', 'nombre_subsubcategoria'];
    
    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class, 'ID_sub', 'ID_sub');
    }
    
    public function productos()
    {
        return $this->hasMany(Producto::class, 'ID_sub2', 'ID_sub2');
    }
}
