<?php

namespace App\Http\Controllers;

use App\Models\Producto;

use Illuminate\Http\Request;
use App\Models\NivelPiramide;
use Illuminate\Http\JsonResponse;

class NivelPiramide_Controller extends Controller
{
    /**
     * Obtiene todos los niveles de la pirámide nutricional.
     *
     * @return JsonResponse
     */
    public function getNivelesPiramide()
    {
        $niveles = NivelPiramide::all();
        return response()->json($niveles,200);
    }

    public function getPiramide($id)
    {
        // Buscar el nivel de la pirámide por ID
        $nivel = NivelPiramide::find($id);

        if (!$nivel) {
            return response("Nivel de pirámide no encontrado", 404);
        }

        // Obtener todos los productos que pertenecen a este nivel
        $productos = Producto::where('idNivel', $id)->get();

        // Retornar la vista con los datos
        return $productos;

    }
}
