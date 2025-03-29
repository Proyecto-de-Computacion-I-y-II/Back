<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class Categoria_Controller extends Controller
{
    public function getAll(){
        $categorias = Categoria::pluck('nombre_categoria');
        return $categorias;
    }

    public function getCategoria(Categoria $id){
            return $id;
    }



}
