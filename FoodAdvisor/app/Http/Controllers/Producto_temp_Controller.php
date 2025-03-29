<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto_temp;

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

    // Buscar productos donde el mes tenga un valor de 1
    $productos = Producto_temp::where($nombreMes, 1)
        ->get(['idTemp', 'producto']); // Seleccionamos solo las columnas necesarias

    return response()->json([
        'mes' => ucfirst($nombreMes),
        'productos' => $productos
    ]);
}


}
