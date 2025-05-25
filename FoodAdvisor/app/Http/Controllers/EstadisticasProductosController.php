<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cesta_Compra;
use App\Models\Producto;
use App\Models\Porcentaje;
use App\Models\NivelPiramide;
use Illuminate\Support\Facades\Log;


use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class EstadisticasProductosController extends Controller
{
    public function obtenerEstadisticas(Request $request)
    {

        // Autenticar al usuario
        $usuario = JWTAuth::parseToken()->authenticate();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Verificar que el usuario tiene rol de administrador
        if ($usuario->rol !== 'admin') {
            return response()->json(['error' => 'No tienes permisos para realizar esta acción'], 403);
        }

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
    //Supermercados en los que se han comprado más productos
    public function supermercadosMasComprados(){

        $supermercados = DB::table('cesta_compra')
            ->join('cesta_productos', 'cesta_compra.ID_cesta', '=', 'cesta_productos.ID_cesta')
            ->join('producto', 'cesta_productos.ID_prod', '=', 'producto.ID_prod')
            ->join('supermercado', 'producto.idSuper', '=', 'supermercado.idSuper')
            ->select('supermercado.nombre_supermercado as supermercado', DB::raw('SUM(cesta_productos.cantidad) as total_comprado'))
            ->groupBy('supermercado.nombre_supermercado')
            ->orderByDesc('total_comprado')
            ->take(5)
            ->get();

        // Retornar los supermercados con la cantidad de productos comprados
        return response()->json([
            'supermercados_mas_comprados' => $supermercados
        ]);
    }

    public function prodMasCompradosPorSupermercado()
    {
        // Obtener los productos más comprados por cada supermercado
        $productosPorSupermercado = DB::table('cesta_compra')
            ->join('cesta_productos', 'cesta_compra.ID_cesta', '=', 'cesta_productos.ID_cesta')
            ->join('producto', 'cesta_productos.ID_prod', '=', 'producto.ID_prod')
            ->join('supermercado', 'producto.idSuper', '=', 'supermercado.idSuper')
            ->select('supermercado.nombre_supermercado as supermercado', 'producto.nombre as producto', DB::raw('SUM(cesta_productos.cantidad) as total_comprado'))
            ->groupBy('supermercado.nombre_supermercado', 'producto.nombre')
            ->orderByDesc('total_comprado')
            ->get();
        // Agrupar los productos por supermercado
        $productosAgrupados = $productosPorSupermercado->groupBy('supermercado');
        // Formatear la respuesta. Debe devolver un json

        $resultado = [];
        foreach ($productosAgrupados as $supermercado => $productos) {
            $resultado[] = [
                'supermercado' => $supermercado,
                'productos' => $productos->map(function ($item) {
                    return [
                        'producto' => $item->producto,
                        'total_comprado' => $item->total_comprado
                    ];
                })->toArray()
            ];
        }
        return response()->json([
            'productos_mas_comprados_por_supermercado' => $resultado
        ]);
    }

    public function productosMasCompradosPorPiramide(){

        // Obtener los productos más comprados por cada nivel de la pirámide
        $productosPorNivel = DB::table('cesta_compra')
            ->join('cesta_productos', 'cesta_compra.ID_cesta', '=', 'cesta_productos.ID_cesta')
            ->join('producto', 'cesta_productos.ID_prod', '=', 'producto.ID_prod')
            ->join('nivel_piramide', 'producto.idNivel', '=', 'nivel_piramide.idNivel')
            ->select('nivel_piramide.Nombre as nivel', 'producto.nombre as producto', DB::raw('SUM(cesta_productos.cantidad) as total_comprado'))
            ->groupBy('nivel_piramide.Nombre', 'producto.nombre')
            ->orderByDesc('total_comprado')
            ->get();

        // Agrupar los productos por nivel de la pirámide
        $productosAgrupados = $productosPorNivel->groupBy('nivel');

        // Formatear la respuesta
        $resultado = [];
        foreach ($productosAgrupados as $nivel => $productos) {
            $resultado[] = [
                'nivel' => $nivel,
                'productos' => $productos->map(function ($item) {
                    return [
                        'producto' => $item->producto,
                        'total_comprado' => $item->total_comprado
                    ];
                })->toArray()
            ];
        }

        return response()->json([
            'productos_mas_comprados_por_nivel' => $resultado
        ]);
    }

    public function obtenerEstadisticasCestas(Request $request)
    {
        // Autenticar al usuario
        $usuario = JWTAuth::parseToken()->authenticate();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Verificar que el usuario tiene rol de administrador
        if ($usuario->rol !== 'admin') {
            return response()->json(['error' => 'No tienes permisos para realizar esta acción'], 403);
        }
        // Calcular la media de los porcentajes por nivel de pirámide antes de las recomendaciones
        $mediasPorNivelNoRecomendado = $this->calcularMediasPorNivelNoRecomendado();
        $mediasPornivel = $this->calcularMediasPorNivel();

        return response()->json([
            'medias_por_nivel_no_recomendado' => $mediasPorNivelNoRecomendado,
            'medias_por_nivel' => $mediasPornivel,
        ]);
    }


    public function obtenerSuperMasEquilibrado()
    {
        // 1. Obtener todos los id de los supermercados
        $supermercados = DB::table('supermercado')->pluck('idSuper');
        $resultados = [];

        // 2. Obtener todos los niveles de la pirámide
        $niveles = DB::table('nivel_piramide')->get();

        // 3. Calcular la distribución teórica ideal basada en el promedio de (minimo + maximo) / 2
        $totalIdeal = $niveles->sum(function ($nivel) {
            return ($nivel->minimo + $nivel->maximo) / 2;
        });

        $distribucionIdeal = $niveles->mapWithKeys(function ($nivel) use ($totalIdeal) {
            $promedio = ($nivel->minimo + $nivel->maximo) / 2;
            return [$nivel->Nombre => round($promedio / $totalIdeal, 4)];
        });

        // 4. Calcular los porcentajes reales para cada supermercado
        foreach ($supermercados as $idSupermercado) {
            $porcentajes = DB::table('cesta_compra')
                ->join('cesta_productos', 'cesta_compra.ID_cesta', '=', 'cesta_productos.ID_cesta')
                ->join('producto', 'cesta_productos.ID_prod', '=', 'producto.ID_prod')
                ->join('nivel_piramide', 'producto.idNivel', '=', 'nivel_piramide.idNivel')
                ->where('producto.idSuper', $idSupermercado)
                ->select('nivel_piramide.Nombre as nivel', DB::raw('SUM(cesta_productos.cantidad) as total_comprado'))
                ->groupBy('nivel_piramide.Nombre')
                ->get();

            $totalComprado = $porcentajes->sum('total_comprado');

            // Calcular porcentajes relativos por nivel del supermercado
            $porcentajesPorNivel = $niveles->mapWithKeys(function ($nivel) use ($porcentajes, $totalComprado) {
                $encontrado = $porcentajes->firstWhere('nivel', $nivel->Nombre);
                $valor = $encontrado ? $encontrado->total_comprado : 0;
                return [$nivel->Nombre => $totalComprado > 0 ? round($valor / $totalComprado, 4) : 0];
            });

            // Guardar en resultados
            $resultados[] = [
                'idSupermercado' => $idSupermercado,
                'porcentajes' => $porcentajesPorNivel
            ];
        }

        // 5. Comparar distribución real con la teórica y calcular desviación total
        $resultadoFinal = [];

        foreach ($resultados as $resultado) {
            $idSupermercado = $resultado['idSupermercado'];
            $porcentajes = $resultado['porcentajes'];
            $desviacion = 0;

            foreach ($distribucionIdeal as $nivel => $ideal) {
                $real = $porcentajes[$nivel] ?? 0;
                $desviacion += abs($real - $ideal);
            }

            $resultadoFinal[] = [
                'idSupermercado' => $idSupermercado,
                'equilibrio' => round($desviacion, 4)
            ];
        }

        // 6. Ordenar por equilibrio (menor desviación = más equilibrado)
        usort($resultadoFinal, function ($a, $b) {
            return $a['equilibrio'] <=> $b['equilibrio'];
        });

        $superMasEquilibrado = $resultadoFinal[0]['idSupermercado'] ?? null;

        return response()->json([
            'supermercado_mas_equilibrado' => $superMasEquilibrado,
            'resultado_final' => $resultadoFinal,
            'resultados' => $resultados,
            'distribucion_ideal' => $distribucionIdeal,
        ]);
    }

    public function procentajeProductosRecomendadosPromedio(){
        // En cesta_productos, la columna 'recomendado' indica si el producto es recomendado o no. Devolver el % de productos recomendados medio de todas las cestas de compra.
        // Primero hay que obtener todas las cestas, calcular el % de productos recomendados en cada cesta y luego calcular la media de esos porcentajes.
        // Obtener todas las cestas de compra
        $cestas = Cesta_Compra::all();
        // Inicializar un array para almacenar los porcentajes de productos recomendados por cesta
        $porcentajesRecomendados = [];
        // Iterar sobre cada cesta de compra
        foreach ($cestas as $cesta) {
            // Calcular el total de productos recomendados en la cesta
            $totalProductosRecomendados = $cesta->productos()
                ->wherePivot('recomendado', true)
                ->distinct('cesta_productos.ID_prod')
                ->count();

            // Calcular el total de productos en la cesta
            $totalProductos = $cesta->productos()
                ->distinct('cesta_productos.ID_prod')
                ->count();

            // Si no hay productos, continuar con la siguiente cesta
            if ($totalProductos === 0) {
                continue;
            }

            // Calcular el porcentaje de productos recomendados en la cesta

            $porcentajeRecomendado = round($totalProductosRecomendados / $totalProductos, 3);
            //Imprimir id de cesta y total de productos recomendados, totales y el porcentaje
            Log::info("Cesta ID: {$cesta->ID_cesta}, Total Recomendados: {$totalProductosRecomendados}, Total Productos: {$totalProductos}, Porcentaje: {$porcentajeRecomendado}");
            $porcentajesRecomendados[] = $porcentajeRecomendado;
        }
        // Calcular la media de los porcentajes de productos recomendados
        // Si está vacío, la media será 0
        if ($porcentajesRecomendados === []) {
            return response()->json([
                'media_porcentaje_recomendado' => 0,
                'porcentajes_recomendados' => []
            ]);
        }
        $mediaPorcentajeRecomendado = count($porcentajesRecomendados) > 0 ? round(array_sum($porcentajesRecomendados) / count($porcentajesRecomendados), 3) : 0;

        return response()->json([
            'media_porcentaje_recomendado' => $mediaPorcentajeRecomendado,
            'porcentajes_recomendados' => $porcentajesRecomendados
        ]);

    }

    public function calcularCosteAdicionalPorEquilibrarse()
    {
        //Para simplificar, devuelve el coste adicional promedio de todos los productos recomendados de las cestas de compra.
        // Obtener todas las cestas de compra
        $cestas = Cesta_Compra::all();
        // Inicializar un array para almacenar los costes adicionales de productos recomendados
        $costesAdicionales = [];
        // Iterar sobre cada cesta de compra
        foreach ($cestas as $cesta) {
            // Obtener los costes adicionales de los productos recomendados
            $costesAdicionalesCesta = Producto::join('cesta_productos', 'producto.ID_prod', '=', 'cesta_productos.ID_prod')
                ->where('cesta_productos.ID_cesta', $cesta->ID_cesta)
                ->where('cesta_productos.recomendado', true)
                ->select(DB::raw('SUM(cesta_productos.cantidad * producto.precio) as coste_adicional'))
                ->value('coste_adicional');

            // Almacenar el coste adicional en el array
            if ($costesAdicionalesCesta !== null) {
                $costesAdicionales[] = $costesAdicionalesCesta;
            }
        }
        // Calcular la media de los costes adicionales
        $mediaCosteAdicional = count($costesAdicionales) > 0 ? round(array_sum($costesAdicionales) / count($costesAdicionales), 3) : 0;
        // Retornar la media de costes adicionales
        return response()->json([
            'media_coste_adicional' => $mediaCosteAdicional,
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