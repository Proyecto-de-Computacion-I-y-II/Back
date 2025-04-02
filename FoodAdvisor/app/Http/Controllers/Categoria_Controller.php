<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class Categoria_Controller extends Controller
{
    //Revisar que devuelva el json completo, no en pluck
    public function getAll() {
        $categorias = Categoria::all();
        return response()->json($categorias);
    }

    public function getCategoria($id){
        $categoria = Categoria::find($id);
        
        if (!$categoria) {
            return response()->json(['error' => 'Categoríax no encontrada'], 404);
        }
    
        return response()->json($categoria, 200);
    }


}
