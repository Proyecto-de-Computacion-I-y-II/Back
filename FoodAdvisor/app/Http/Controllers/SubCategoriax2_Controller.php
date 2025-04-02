<?php

namespace App\Http\Controllers;

use App\Models\Subcategoriax2;
use Illuminate\Http\Request;

class SubCategoriax2_Controller extends Controller
{
    //Revisar que devuelva el json completo, no en pluck
    public function getAll() {

        $categorias = Subcategoriax2::all();
        return response()->json($categorias);
    }

    //Sobra
    public function getSubCategoriasx2($id){
            $categoria = Subcategoriax2::find($id);
            
            if (!$categoria) {
                return response()->json(['error' => 'Subcategoríax2 no encontrada'], 404);
            }
        
            return response()->json($categoria, 200);
        }

    //Resumen
    //Falta createSubcategoryx2
}
