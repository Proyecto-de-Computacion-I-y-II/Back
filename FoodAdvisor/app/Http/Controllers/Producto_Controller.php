<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Producto;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class Producto_Controller extends Controller
{
public function getAll()
{
    $productos = Producto::paginate(60);

    return response()->json([
        'productos' => $productos->items(), // Devuelve solo los elementos de la página actual
        'total_paginas' => $productos->lastPage(), // Devuelve el número total de páginas
        'pagina_actual' => $productos->currentPage(), // Devuelve el número de la página actual
        'total_elementos' => $productos->total(), // Devuelve el número total de elementos
        'elementos_por_pagina' => $productos->perPage(), // Devuelve la cantidad de elementos por página
    ], 200);
}

    public function getProducto($id)
    {
        $productos = Producto::with('supermercado','temporada','nivelPiramide')
        ->find($id);
        
        if (!$productos) {
            return response()->json(['message' => 'producto no encontrado'], 404);
        }

        return response()->json($productos, 200);

    }

    public function postProducto(Request $request)
    {
        $validatedData = $request->validate([
            'ID_sub2' => 'required|integer',
            'idSuper' => 'required|integer',
            'idNivel' => 'required|integer',
            'idTemp' => 'nullable|integer',
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'href' => 'nullable|string',
            'imagen' => 'nullable|string',
            'kg' => 'nullable|numeric',
            'l' => 'nullable|numeric',
            'ud' => 'nullable|integer',
            'grasas' => 'nullable|numeric',
            'acidos_grasos' => 'nullable|numeric',
            'fibra' => 'nullable|numeric',
            'ingredientes' => 'nullable|string',
            'hidratos_carbono' => 'nullable|numeric',
            'azucares' => 'nullable|numeric',
            'sal' => 'nullable|numeric',
            'proteinas' => 'nullable|numeric',
        ]);

        $producto = Producto::create($validatedData);

        return response()->json(['mensaje' => 'producto creado correctamente', 'producto' => $producto], 201);
    }

    
    public function getSimilares(Producto $id)
    {
        $pythonScript = storage_path('app/private/predict.py');
        
        $pythonPath = 'python';
        $output = shell_exec("$pythonPath $pythonScript " . escapeshellarg($id->ID_prod-1). " 2>&1");

        // Convertir la salida JSON en un array PHP
        $similarIds = json_decode($output, true);

        // Retornar solo los 5 IDs
        return response()->json($similarIds);
    } 
    
    public function filtrarProductos(Request $request)
    {
        $query = Producto::query();

    // Filtro por precio
    if ($request->has('precio_min')) {
        $query->where('precio', '>=', $request->precio_min);
    }
    if ($request->has('precio_max')) {
        $query->where('precio', '<=', $request->precio_max);
    }

    // Filtro por nivel de pirámide
    if ($request->has('idNivel')) {
        $niveles = $request->idNivel; // Suponiendo que 'idsNivel' es un array de IDs de nivel
        $query->whereIn('idNivel', $niveles);
    }

    // Filtro por supermercado
    if ($request->has('idSuper')) {
        $supermercados = $request->idSuper; // Suponiendo que 'idsSuper' es un array de IDs
        $query->whereIn('idSuper', $supermercados);
    }

    // Filtros por valores nutricionales
    $nutrientes = ['grasas', 'acidos_grasos', 'hidratos_carbono', 'azucares', 'proteinas', 'sal', 'fibra'];
    foreach ($nutrientes as $nutriente) {   //porque lo importaremos de la bd
        if ($request->has("{$nutriente}_min")) {
            $query->where($nutriente, '>=', $request->input("{$nutriente}_min"));
        }
        if ($request->has("{$nutriente}_max")) {
            $query->where($nutriente, '<=', $request->input("{$nutriente}_max"));
        }
    }

    //Filtro por nombre
    if ($request->has('nombre')) {
        $nombre = $request->nombre;
        $query->where('nombre', 'ILIKE', "$nombre%")  // Prioriza coincidencias exactas
              ->orWhere('nombre', 'ILIKE', "%$nombre%") // Si no hay, pasa a coincidencias parciales
              ->orderByRaw("CASE 
                    WHEN nombre ILIKE ? THEN 1 
                    WHEN nombre ILIKE ? THEN 2 
                    ELSE 3 END", ["$nombre", "$nombre%"]);  // Ordena por prioridad: 1) coincidencia exacta 2) coincidencia parcial y 3) productos que solo contienen la palabra
    }    

    $productos = $query->paginate(60);   //Paginacion de los resultados (si son muchos no funciona)

    return response()->json([
        'productos' => $productos->items(), // Devuelve solo los elementos de la página actual
        'total_paginas' => $productos->lastPage(), // Devuelve el número total de páginas
        'pagina_actual' => $productos->currentPage(), // Devuelve el número de la página actual
        'total_elementos' => $productos->total(), // Devuelve el número total de elementos
        'elementos_por_pagina' => $productos->perPage(), // Devuelve la cantidad de elementos por página
    ], 200);
}

public function getTotalProductosComprados()
{
    try {
        // Autenticar usuario desde el token JWT
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Sumar cantidad de productos de todas sus cestas
        $totalProductos = DB::table('cesta_compra')
            ->join('cesta_productos', 'cesta_compra.ID_cesta', '=', 'cesta_productos.ID_cesta')
            ->where('cesta_compra.ID_user', $user->ID_user)
            ->sum('cesta_productos.cantidad');

        return response()->json([
            'total_comprado' => $totalProductos
        ]);

    } catch (\Exception $e) {
        return response()->json($e->getMessage(), 500);
    }
}


public function getMaxValues()
{
    $maxValues = Producto::selectRaw('
            MAX(precio) as precio,
            MAX(grasas) as grasas,
            MAX(acidos_grasos) as acidos_grasos,
            MAX(fibra) as fibra,
            MAX(hidratos_carbono) as hidratos_carbono,
            MAX(azucares) as azucares,
            MAX(sal) as sal,
            MAX(proteinas) as proteinas
        ')
        ->first();

    return response()->json($maxValues);
}

public function getNombreSupermercado($id)
{
    $producto = Producto::with('supermercado')->find($id);

    if (!$producto) {
        return response()->json(['message' => 'Producto no encontrado'], 404);
    }

    return response()->json([
        'supermercado' => $producto->supermercado->nombre_supermercado ?? 'Sin supermercado'
    ]);
}


}