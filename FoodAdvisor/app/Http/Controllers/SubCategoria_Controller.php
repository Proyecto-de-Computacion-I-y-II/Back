<?php

namespace App\Http\Controllers;

use App\Models\Subcategoria;
use Illuminate\Http\Request;

class SubCategoria_Controller extends Controller
{
    public function getAll(){
        $categorias = Subcategoria::pluck('nombre_subcategoria');
        return $categorias;
    }

    public function getSubCategoria(Subcategoria $id){
            return $id;
    }
}
