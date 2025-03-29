<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;
    
    protected $table = 'producto';
    protected $primaryKey = 'ID_prod';
    protected $fillable = ['ID_sub2', 'idSuper', 'idNivel', 'idTemp', 'nombre', 'precio', 'href', 'imagen', 'kg', 'l', 'ud', 'grasas', 'acidos_grasos', 'fibra', 'ingredientes', 'hidratos_carbono', 'azucares', 'sal', 'proteinas'];
    
    public function temporada()
    {
        return $this->belongsTo(Producto_temp::class, 'idTemp', 'idTemp');
    }
    
    public function supermercado()
    {
        return $this->belongsTo(Supermercado::class, 'idSuper', 'idSuper');
    }
    
    public function nivelPiramide()
    {
        return $this->belongsTo(NivelPiramide::class, 'idNivel', 'idNivel');
    }
    
    public function subcategoria()
    {
        return $this->belongsTo(Subcategoriax2::class, 'ID_sub2', 'ID_sub2');
    }
    
    public function cestas()
    {
        return $this->belongsToMany(Cesta_Compra::class, 'cesta_productos', 'ID_prod', 'ID_cesta');
    }
}

