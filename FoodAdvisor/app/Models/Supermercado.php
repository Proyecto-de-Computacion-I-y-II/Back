<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supermercado extends Model
{
    use SoftDeletes;
    protected $table = 'supermercado';
    protected $primaryKey = 'idSuper';
    protected $fillable = ['nombre_supermercado'];
    
    public function productos()
    {
        return $this->hasMany(Producto::class, 'idSuper', 'idSuper');
    }
}
