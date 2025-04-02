<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Producto;

class Producto_Controller extends Controller
{
    public function getAll()
    {
        $productos = Producto::paginate(50);    //para hacer consultas por pagina, hacer /api/productos?page=x, siendo x el numero de pagina
        return response()->json($productos, 200);
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
        $query->where('idNivel', $request->idNivel);
    }

    // Filtro por supermercado
    if ($request->has('idSuper')) {
        $query->where('idSuper', $request->idSuper);
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

    $producto = $query->paginate(200);   //Paginacion de los resultados (si son muchos no funciona)

    return response()->json($producto);
}

public function getTopSellers()
{
    $topSellers = DB::table('cesta_compra') // Sin comillas dobles aquí
        ->join('cesta_productos', 'cesta_compra.ID_cesta', '=', 'cesta_productos.ID_cesta')
        ->join('usuario', 'cesta_compra.ID_user', '=', 'usuario.ID_user')
        ->select(
            'usuario.ID_user',
            'usuario.nombre',
            'usuario.apellidos',
            DB::raw('SUM("cesta_productos"."cantidad") as total_comprado') // Solo aquí con comillas dobles
        )
        ->groupBy('usuario.ID_user', 'usuario.nombre', 'usuario.apellidos')
        ->orderByDesc('total_comprado')
        ->take(10)
        ->get();

    return response()->json($topSellers);
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

}