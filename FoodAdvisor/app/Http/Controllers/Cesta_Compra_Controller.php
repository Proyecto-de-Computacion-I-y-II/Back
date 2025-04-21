<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cesta_Compra;
use App\Models\Producto;
use App\Models\NivelPiramide;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;



class Cesta_Compra_Controller extends Controller
{
    //Sobra
    public function getAll()
    {
        $cestas = Cesta_Compra::all();
        return response()->json($cestas,200);
    }

    //Revisar que el valor que devuelva, lo devuelva con los productos asociados cargados y valida que el id recibido exista
    public function getById($id)
    {
        $usuario = JWTAuth::parseToken()->authenticate();
    
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 404);
        }

        $cesta = Cesta_Compra::with('productos')
        ->where('ID_user', $usuario->ID_user)
        ->where('ID_cesta', $id)
        ->whereNull('deleted_at')
        ->get();

        return response()->json(['cesta' => $cesta], 200);
    }
    //Sobra, integrar en getById
    public function getProdFromCesta(Cesta_Compra $cesta)
    {
        $usuario = JWTAuth::parseToken()->authenticate();
    
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 404);
        }

        $cestaValidada = Cesta_Compra::with('productos')
        ->where('ID_user', $usuario->ID_user)
        ->where('ID_cesta', $cesta->ID_cesta)
        ->whereNull('deleted_at')
        ->get();

        if(!$cestaValidada){
            return response()->json(['mensaje'=>'Error: Cesta no encontrada', 404]);
        }
        return response()->json($cestaValidada->productos,200);
    }

    //Revisar que se valide el ID_user que exista. Sol: required ya busca que la foreign key exista. No hay que cambiar
    public function store(Request $req) {
        $validator = Validator::make($req->all(), [
            'ID_user' => 'required|integer|min:1',
            'fecha_compra' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos de entrada no válidos', 'errors' => $validator->errors()], 422);
        }
        

        $cesta = Cesta_Compra::create([
            'ID_user' => $req['ID_user'],
            'fecha_compra' => $req['fecha_compra'],
        ]);

        if (!$cesta) {
            return response()->json(['error' => 'No se pudo crear la cesta de la compra'], 500);
        }

        return response()->json($cesta, 201);
    }

    //Revisar la funcionalidad de que si extiste añadir cantidad o devolver error de que el producto ya existe en canasta
    public function storeInCesta(Cesta_Compra $cesta, Request $req) {
        $usuario = JWTAuth::parseToken()->authenticate();
    
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 404);
        }

        $validator = Validator::make($req->all(), [
            'ID_prod' => 'required|exists:producto,ID_prod',
            'cantidad' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos de entrada no válidos', 'errors' => $validator->errors()], 422);
        }
        
        $cesta->load('productos');
        
        if(!$cesta){
            return response()->json(['mensaje'=>'Error: Cesta no encontrada'], 404);
        }

        $cestaValidada = Cesta_Compra::with('productos') //se asegura que está accediendo a una cesta suya propia (y no la de otro)
        ->where('ID_user', $usuario->ID_user)
        ->where('ID_cesta',$cesta->ID_cesta)
        ->whereNull('cesta_compra.deleted_at')
        ->first();

        if(!$cestaValidada){
            return response()->json(['mensaje'=>'Error: Cesta no encontrada'], 404);
        }

        $prod = Producto::find($req->ID_prod);

        if(!$prod){
            return response()->json(['mensaje'=>'Error: Producto a insertar no encontrado'], 404);
        }

        $pivot = $cesta->productos()
        ->wherePivot('ID_prod', $prod->ID_prod)
        ->whereNull('cesta_productos.deleted_at')
        ->orderByDesc('ID_cesta')
        ->first();

        if($pivot) {
            $curr_cant = $pivot->pivot->cantidad;
            $new_cant = $curr_cant+$req->cantidad;
            $cesta->productos()->updateExistingPivot($prod->ID_prod, ['cantidad' => $new_cant]);
        } else {
            $cesta->productos()->attach($prod->ID_prod, ['cantidad' => $req->cantidad]);
        }

        $cesta->calcularPorcentajes();
        return response()->json(['mensaje' => 'producto añadido a la cesta correctamente'], 200);
    }

    //Mejor usar este.
    public function updateProdFromCesta(Cesta_Compra $cesta, Request $req)
    {
        $usuario = JWTAuth::parseToken()->authenticate();
    
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 404);
        }

        $cesta->load('productos');
        if(!$cesta){
            return response()->json(['mensaje'=>'Error: Cesta no encontrada'], 404);
        }

        $cestaValidada = Cesta_Compra::with('productos') //se asegura que está accediendo a una cesta suya propia (y no la de otro)
        ->where('ID_user', $usuario->ID_user)
        ->where('ID_cesta',$cesta->ID_cesta)
        ->whereNull('cesta_compra.deleted_at')
        ->first();

        if(!$cestaValidada){
            return response()->json(['mensaje'=>'Error: Cesta no encontrada'], 404);
        }
        
        $validatedProd = $req->validate([
            'ID_prod' => 'required|exists:producto,ID_prod',
            'cantidad' => 'required|integer|min:1',
        ]);

        $prod = Producto::find($validatedProd['ID_prod']);

        if (!$prod) {
            return response()->json(['mensaje' => 'Error: Producto no encontrado'], 404);
        }

        $pivotData = $cesta->productos()->wherePivot('ID_prod', $prod->ID_prod)->first();

        $cesta->calcularPorcentajes();

        if ($pivotData) {
            $cesta->productos()->updateExistingPivot($prod->ID_prod, ['cantidad' => $validatedProd['cantidad']]);
            $cesta->productos()->syncWithoutDetaching([
                $prod->ID_prod => ['cantidad' => $validatedProd['cantidad']]
            ]);
            return response()->json(['mensaje' => 'Cantidad actualizada correctamente'], 200);

        } else {
            return response()->json(['mensaje' => 'El producto no está en la cesta'], 404);
        }
    }

    //Sobra
    public function updateCesta(Request $req, $id)
    {
        $cesta = Cesta_compra::find($id);
        if(!$cesta){
            return response()->json(["mensaje" => "Error: Cesta no encontrada", 404]);
        }
        $cestaValidada = $req->validate([
            'ID_user' => 'required|integer|min:1',
            'fecha_compra' => 'required|date'
        ]);

        $cesta->update($cestaValidada);
        return response()->json(["mensaje" => "Cesta actualizada"], 200);
    }

    public function deleteCesta(Cesta_Compra $cesta) {
        
        $usuario = JWTAuth::parseToken()->authenticate();
    
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 404);
        }
        
        $cestaValidada = Cesta_Compra::with('productos') //se asegura que está accediendo a una cesta suya propia (y no la de otro)
        ->where('ID_user', $usuario->ID_user)
        ->where('ID_cesta',$cesta->ID_cesta)
        ->whereNull('cesta_compra.deleted_at')
        ->first();

        if(!$cestaValidada){
            return response()->json(['mensaje'=>'Error: Cesta no encontrada'], 404);
        }

        $cesta->delete();

        return response()->json(['mensaje' => 'Cesta eliminada correctamente'], 200);
    }

    public function removeProductoFromCesta(Cesta_Compra $cesta, $productoId) {
        if (!$cesta) {
            return response()->json(['mensaje' => 'Error: Cesta no encontrada'], 404);
        }

        $productInCesta = $cesta->productos()->wherePivot('ID_prod', $productoId)->first();

        if (!$productInCesta) {
            return response()->json(['mensaje' => 'Error: Producto no encontrado en la cesta'], 404);
        }

        $cesta->productos()->detach($productoId);

        return response()->json(['mensaje' => 'producto eliminado correctamente de la cesta'], 200);
    }

    //Revisar si se queda
    public function getHistorialCompras(Request $request)
    {
        $usuario = $request->user();
        $compras = Cesta_Compra::where('ID_user', $usuario->ID_user)->get();

        foreach ($compras as $compra) {
            $compra->load('productos');
        }

        return response()->json($compras, 200);
    }

    public function recomendacion(Cesta_Compra $cesta): JsonResponse
    {
        // Obtener los Niveles y sus Porcentajes Recomendados desde la BBDD
        $nivelesPiramide = NivelPiramide::all(); // Obtiene todos los niveles de la tabla

        // Validar si se obtuvieron niveles
        if ($nivelesPiramide->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron niveles de pirámide definidos en la base de datos.',
                'recomendaciones' => [],
            ], 404); // Error porque falta configuración básica
        }

        // Obtener Productos y Calcular Porcentajes Actuales en la Cesta
        // Asegúrate de que la relación 'productos' se carga con la cantidad en el pivot
        $productosEnCesta = $cesta->productos()->withPivot('cantidad')->get();
        $totalProductos = 0;

        foreach ($productosEnCesta as $producto) {
            if ($producto && isset($producto->pivot) && isset($producto->pivot->cantidad)) {
                $totalProductos += $producto->pivot->cantidad;
            } else {
                // Log o manejo de error si un producto no tiene información en el pivot
                Log::error('Error: Producto sin información de pivot en la cesta.', ['producto' => $producto]);
                return response()->json([
                    'message' => 'Error al procesar la cesta. Contacte al administrador.',
                    'recomendaciones' => [],
                ], 500);
            }
        }

        $productosPorNivel = [];

        if ($totalProductos > 0) {
            $cantidadesPorNivel = $productosEnCesta->groupBy('idNivel')
                ->map(function ($group) {
                    $totalNivel = 0;
                    foreach ($group as $producto) {
                        if ($producto && isset($producto->pivot) && isset($producto->pivot->cantidad)) {
                            $totalNivel += $producto->pivot->cantidad;
                        } else {
                            // Esto no debería ocurrir si la carga eager loading es correcta
                            Log::error('Error: Producto sin información de pivot dentro del groupBy.', ['producto' => $producto]);
                            // Puedes optar por lanzar una excepción o manejarlo de otra manera
                        }
                    }
                    return $totalNivel;
                });

            foreach ($cantidadesPorNivel as $idNivel => $cantidad) {
                $productosPorNivel[$idNivel] = ($cantidad / $totalProductos) * 100;
            }
        } else {
            return response()->json([
                'message' => 'La cesta está vacía. No se pueden generar recomendaciones.',
                'recomendaciones' => [],
            ], 200);
        }

        // Identificar Niveles Deficitarios comparando con la BBDD ---
        $nivelesDeficitarios = [];
        // Itera sobre los niveles obtenidos de la base de datos
        foreach ($nivelesPiramide as $nivel) {
            // Accede a la clave primaria usando getKey() o directamente si sabes el nombre (idNivel)
            $idNivelActual = $nivel->idNivel;
            $porcentajeActual = $productosPorNivel[$idNivelActual] ?? 0;

            // Compara con el mínimo almacenado en la tabla nivelPiramide
            // Asegúrate que las columnas se llaman 'minimo' y 'maximo'
            if ($porcentajeActual < $nivel->minimo) {
                $nivelesDeficitarios[] = $idNivelActual;
            }
            // Podrías añadir lógica para niveles con exceso si lo necesitas:
            // else if ($porcentajeActual > $nivel->maximo) { ... }
        }

        // Obtener Recomendaciones Aleatorias de los Niveles Deficitarios ---
        $recomendacionesPorNivel = []; // Array para almacenar recomendaciones por nivel
        if (!empty($nivelesDeficitarios)) {
            $idsProductosEnCesta = $productosEnCesta->pluck('ID_prod')->toArray(); // Ajusta 'ID_prod'

            foreach ($nivelesDeficitarios as $nivelId) {
                $productosRecomendados = Producto::where('idNivel', $nivelId) // Ajusta 'idNivel'
                    ->whereNotIn('ID_prod', $idsProductosEnCesta) // Ajusta 'ID_prod'
                    ->inRandomOrder()
                    ->take(4)
                    ->select(['ID_prod', 'nombre', 'precio', 'imagen', 'idNivel']) // Ajusta campos
                    ->get();

                if (!$productosRecomendados->isEmpty()) {
                    $recomendacionesPorNivel[$nivelId] = $productosRecomendados;
                }
            }
        }

        // Devolver Respuesta JSON ---
        if (empty($recomendacionesPorNivel) && !empty($nivelesDeficitarios)) {
            return response()->json([
                'message' => 'No se encontraron productos adicionales para recomendar en los niveles deficitarios.',
                'recomendaciones' => [],
            ]);
        } elseif (empty($recomendacionesPorNivel) && empty($nivelesDeficitarios)) {
            return response()->json([
                'message' => '¡Tu cesta cumple con los porcentajes recomendados!',
                'recomendaciones' => [],
            ]);
        } else {
            return response()->json([
                'recomendaciones' => $recomendacionesPorNivel,
            ]);
        }
    }


    //Resumen
    //Falta getCestasByToken
}