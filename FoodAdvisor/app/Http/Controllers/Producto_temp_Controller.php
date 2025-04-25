<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto_temp;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Producto_temp_Controller extends Controller
{
    // Método para obtener todos los productos de temporada
    public function getAllProducts()
    {
        $productos = Producto_temp::all(); // Obtener todos los registros de la tabla
        return response()->json(['productos' => $productos]);
    }
    

    // Método para obtener los productos de temporada por mes
    public function getProductsByMonth($mes)
    {
        $meses = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
    
        if (!isset($meses[$mes])) {
            return response()->json(['error' => 'Mes no válido'], 400);
        }
    
        $nombreMes = $meses[$mes];
    
        // Productos con valor 1 en ese mes
        $productos = Producto_temp::where($nombreMes, 1)
            ->get(['idTemp', 'producto']);
    
        // Productos con valor 0 en ese mes
        $productossaliendo = Producto_temp::where($nombreMes, 0)
            ->get(['idTemp', 'producto']);
    
        return response()->json([
            'mes' => ucfirst($nombreMes),
            'productos' => $productos,
            'productossaliendo' => $productossaliendo
        ]);
    }
    

// Método para obtener imagen, nombre e idSuper de la tabla productos
public function getDetalles($idTemp)
{
    // Buscar todos los productos de temporada con el idTemp
    $productosTemp = Producto_temp::where('idTemp', $idTemp)->get(); // Filtrar por idTemp

    if ($productosTemp->isEmpty()) {
        return response()->json(['message' => 'No se encontraron productos de temporada para este ID de temporada'], 404);
    }

    // Crear un array para almacenar los detalles de los productos
    $productosDetalles = [];

    // Recorrer cada producto de temporada encontrado
    foreach ($productosTemp as $productoTemp) {
        // Buscar todos los productos correspondientes en la tabla productos usando el idTemp
        $productos = Producto::where('idTemp', $productoTemp->idTemp)->get();  // Buscar todos los productos con el mismo idTemp

        foreach ($productos as $producto) {
            $productosDetalles[] = [
                'id' => $producto->ID_prod,
                'nombre' => $producto->nombre,
                'imagen' => $producto->imagen,
                'idSuper' => $producto->idSuper
            ];
        }
    }

    return response()->json($productosDetalles);  // Devolver todos los productos encontrados
}

public function getProductosDelMes()
{
    // Obtener el nombre del mes actual en inglés (ej. "April")
    $nombreMes = Carbon::now()->format('F');

    // Consultar productos donde el valor del mes actual sea 1
    $productos = DB::table('productos_temp')
    ->where($nombreMes, 1)
    ->select('idTemp', 'producto')
    ->get();

    // Retornar como JSON
    return response()->json([
        'mes' => $nombreMes,
        'productos' => $productos
    ]);
}

}