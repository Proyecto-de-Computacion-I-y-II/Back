<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Cesta_Compra extends Model
{
    use SoftDeletes;

    protected $table = 'cesta_compra';
    protected $primaryKey = 'ID_cesta';
    protected $fillable = [
        'ID_user',
        'fecha_compra',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'ID_user', 'ID_user');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'cesta_productos', 'ID_cesta', 'ID_prod')
            ->withPivot('cantidad');
    }

    public function porcentajes()
    {
        return $this->hasMany(Porcentaje::class, 'id_cesta', 'ID_cesta');
    }

    public function calcularPorcentajes()
    {
        // Calcular el total de productos en base a kg o l directamente desde la base de datos
        $totalProductos = DB::table('cesta_productos')
            ->join('producto', 'cesta_productos.ID_prod', '=', 'producto.ID_prod')
            ->where('cesta_productos.ID_cesta', $this->ID_cesta)
            ->whereNull('producto.deleted_at')
            ->selectRaw('SUM(CASE WHEN producto.kg > 0 THEN cesta_productos.cantidad * producto.kg ELSE cesta_productos.cantidad * producto.l END) as total')
            ->value('total') ?? 0;

        if ($totalProductos === 0) {
            return $this->actualizarPorcentajesCero();
        }

        // Obtener niveles y calcular la cantidad total por nivel (considerando kg o l) en una sola consulta
        $conteosPorNivel = Producto::join('nivel_piramide', 'producto.idNivel', '=', 'nivel_piramide.idNivel')
            ->join('cesta_productos', 'producto.ID_prod', '=', 'cesta_productos.ID_prod')
            ->where('cesta_productos.ID_cesta', $this->ID_cesta) // Asegurar que solo se consideran los productos de esta cesta
            ->whereNull('producto.deleted_at') // Filtra productos no borrados
            ->select(
                'nivel_piramide.idNivel',
                DB::raw('SUM(CASE WHEN producto.kg > 0 THEN cesta_productos.cantidad * producto.kg ELSE cesta_productos.cantidad * producto.l END) as total')
            )
            ->groupBy('nivel_piramide.idNivel')
            ->get();

        // Calcular e insertar porcentajes en la tabla 'porcentajes'
        foreach ($conteosPorNivel as $item) {
            Porcentaje::updateOrCreate(
                ['ID_cesta' => $this->ID_cesta, 'idNivel' => $item->idNivel],
                ['porcentaje' => $item->total / $totalProductos]
            );
        }
    }

    protected function actualizarPorcentajesCero()
    {
        // Obtener todos los niveles desde la base de datos
        $niveles = NivelPiramide::all();

        // Insertar porcentajes cero en la tabla 'porcentajes'
        foreach ($niveles as $nivel) {
            Porcentaje::updateOrCreate(
                ['ID_cesta' => $this->ID_cesta, 'idNivel' => $nivel->idNivel],
                ['porcentaje' => 0]
            );
        }
    }
}