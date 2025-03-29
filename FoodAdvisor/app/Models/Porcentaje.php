<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Porcentaje extends Model
{
    use HasFactory;

    protected $table = 'porcentajes'; // Especifica el nombre de la tabla
    protected $fillable = ['ID_cesta', 'idNivel', 'porcentaje']; // Especifica los campos que se pueden llenar

    // Define las relaciones con Cesta_Compra y NivelPiramide
    public function cestaCompra()
    {
        return $this->belongsTo(Cesta_Compra::class, 'ID_cesta', 'ID_cesta');
    }

    public function nivelPiramide()
    {
        return $this->belongsTo(NivelPiramide::class, 'idNivel', 'idNivel');
    }
}