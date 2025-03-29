<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cesta_Compra;
use App\Models\Producto;
use App\Models\Porcentaje;
use App\Models\NivelPiramide;

class EstadisticasProductosController extends Controller
{
    public function obtenerEstadisticas(Request $request)
    {
        // Obtener la cantidad de veces que aparece cada producto en la tabla Cesta_Productos
        $productosVendidos = DB::table('cesta_productos')
            ->select('ID_prod', 'cantidad')
            ->get();

        // Ordenar y obtener los productos más vendidos
        $masVendidos = $productosVendidos->sortByDesc('cantidad')->values()->take(5)->all();

        // Ordenar y obtener los productos menos vendidos
        $menosVendidos = $productosVendidos->sortBy('cantidad')->values()->take(5)->all();

        // Calcular el total de productos vendidos
        $totalProductosVendidos = $productosVendidos->sum('cantidad');

        // Calcular el promedio de productos vendidos por producto
        $promedioVentasPorProducto = $productosVendidos->avg('cantidad');


        return response()->json([
            'mas_vendidos' => $masVendidos,
            'menos_vendidos' => $menosVendidos,
            'total_productos_vendidos' => $totalProductosVendidos,
            'promedio_ventas_por_producto' => $promedioVentasPorProducto,
        ]);
    }

    public function obtenerEstadisticasCestas(Request $request)
    {
        // Calcular la media de los porcentajes por nivel de pirámide antes de las recomendaciones
        $mediasPorNivelNoRecomendado = $this->calcularMediasPorNivelNoRecomendado();
        $mediasPornivel = $this->calcularMediasPorNivel();

        return response()->json([
            'medias_por_nivel_no_recomendado' => $mediasPorNivelNoRecomendado,
            'medias_por_nivel' => $mediasPornivel,
        ]);
    }

    private function calcularMediasPorNivelNoRecomendado()
    {
        // Obtener todas las cestas de compra
        $cestas = Cesta_Compra::all();

        // Inicializar un array para almacenar los porcentajes por nivel
        $porcentajesPorNivel = [];

        // Iterar sobre cada cesta de compra
        foreach ($cestas as $cesta) {
            // Calcular el total de productos no recomendados en la cesta
            $totalProductos = $cesta->productos()
                ->wherePivot('recomendado', false)
                ->sum('cesta_productos.cantidad');

            // Si no hay productos no recomendados, continuar con la siguiente cesta
            if ($totalProductos === 0) {
                continue;
            }

            // Obtener niveles y contar productos no recomendados por nivel
            $conteosPorNivel = Producto::join('nivel_piramide', 'producto.idNivel', '=', 'nivel_piramide.idNivel')
                ->join('cesta_productos', 'producto.ID_prod', '=', 'cesta_productos.ID_prod')
                ->where('cesta_productos.ID_cesta', $cesta->ID_cesta)
                ->where('cesta_productos.recomendado', false)
                ->whereNull('producto.deleted_at')
                ->select('nivel_piramide.idNivel', DB::raw('SUM(cesta_productos.cantidad) as total'))
                ->groupBy('nivel_piramide.idNivel')
                ->get();

            // Calcular porcentajes y almacenarlos por nivel
            foreach ($conteosPorNivel as $item) {
                // Calcular porcentaje con 3 decimales
                $porcentaje = round($item->total / $totalProductos, 3);
                if (!isset($porcentajesPorNivel[$item->idNivel])) {
                    $porcentajesPorNivel[$item->idNivel] = [];
                }
                $porcentajesPorNivel[$item->idNivel][] = $porcentaje;
            }
        }

        // Calcular la media de los porcentajes por nivel
        $mediasPorNivel = [];
        foreach ($porcentajesPorNivel as $nivelId => $porcentajes) {
            $mediasPorNivel[$nivelId] = count($porcentajes) > 0 ? round(array_sum($porcentajes) / count($porcentajes), 3) : 0;
        }

        // Normalizar para asegurar que la suma no exceda 1
        $totalMedia = array_sum($mediasPorNivel);
        if ($totalMedia > 1) {
            foreach ($mediasPorNivel as $nivelId => $media) {
                $mediasPorNivel[$nivelId] = round($media / $totalMedia, 3);
            }
        }

        return $mediasPorNivel;
    }
    private function calcularMediasPorNivel()
    {
        // Obtener todos los niveles de pirámide
        $niveles = NivelPiramide::all();

        // Inicializar un array para almacenar los porcentajes por nivel
        $porcentajesPorNivel = [];

        // Iterar sobre cada nivel
        foreach ($niveles as $nivel) {
            // Obtener todos los porcentajes para este nivel
            $porcentajes = Porcentaje::where('idNivel', $nivel->idNivel)->pluck('porcentaje')->toArray();

            // Calcular la media de los porcentajes para este nivel con 3 decimales
            $media = count($porcentajes) > 0 ? round(array_sum($porcentajes) / count($porcentajes), 3) : 0;

            // Almacenar la media en el array
            $porcentajesPorNivel[$nivel->idNivel] = $media;
        }

        // Normalizar para asegurar que la suma no exceda 1
        $totalMedia = array_sum($porcentajesPorNivel);
        if ($totalMedia > 1) {
            foreach ($porcentajesPorNivel as $nivelId => $media) {
                $porcentajesPorNivel[$nivelId] = round($media / $totalMedia, 3);
            }
        }

        return $porcentajesPorNivel;
    }
}