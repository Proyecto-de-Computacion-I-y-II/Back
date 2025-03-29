<?php

namespace App\Http\Controllers;

use App\Models\Subcategoriax2;
use Illuminate\Http\Request;

class SubCategoriax2_Controller extends Controller
{
    public function getAll(){
        $categorias = SubCategoriax2::pluck('nombre_subsubcategoria');
        return $categorias;
    }

    public function getSubCategoriasx2(Subcategoriax2 $id){
            return $id;
    }
}
