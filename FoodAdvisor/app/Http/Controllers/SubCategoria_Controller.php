<?php

namespace App\Http\Controllers;

use App\Models\Subcategoria;
use Illuminate\Http\Request;

class SubCategoria_Controller extends Controller
{
    //Revisar que devuelva el json completo, no en pluck
    public function getAll() {
        $categorias = Subcategoria::all();
        return response()->json($categorias);
    }

    //Sobra
    public function getSubCategoria($id){
        $categoria = Subcategoria::find($id);
        
        if (!$categoria) {
            return response()->json(['error' => 'Subcategoría no encontrada'], 404);
        }
    
        return response()->json($categoria, 200);
    }
    

    //Resumen
    //Falta createSubcategory
}
