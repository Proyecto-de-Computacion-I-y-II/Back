<?php

namespace App\Http\Controllers;

use App\Models\Supermercado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Supermercado_Controller extends Controller
{
    public function getAll(){
        $supermercados = Supermercado::all();
        return response()->json($supermercados);
    }

    public function getById($id){
        $supermercado = Supermercado::find($id);

        if (!$supermercado) {
            return response()->json(['message' => 'supermercado no encontrado'], 404);
        }

        return response()->json($supermercado);
    }

    public function create(Request $request){
        $request->validate([
            'nombre_supermercado' => 'required|string|max:255',
        ]);

        $supermercado = Supermercado::create([
            'nombre_supermercado' => $request->nombre_supermercado,
        ]);

        return response()->json($supermercado, 201);
    }

    public function getCategoriaArbol($idSuper)
    {
        // Obtener productos del supermercado especificado (solo para identificar las categorías relacionadas)
        $productos = DB::table('producto')
            ->select('producto.ID_sub2', 'subcategoria2.ID_sub', 'subcategoria.ID_cat')
            ->join('subcategoria2', 'producto.ID_sub2', '=', 'subcategoria2.ID_sub2')
            ->join('subcategoria', 'subcategoria2.ID_sub', '=', 'subcategoria.ID_sub')
            ->where('producto.idSuper', $idSuper)
            ->get();
        
        // Extraer IDs únicos de categorías, subcategorías y subcategorías nivel 2 de los productos
        $catIds = $productos->pluck('ID_cat')->unique()->toArray();
        $subIds = $productos->pluck('ID_sub')->unique()->toArray();
        $sub2Ids = $productos->pluck('ID_sub2')->unique()->toArray();
        
        // Obtener solo las categorías, subcategorías y subcategorías nivel 2 relacionadas con los productos
        $categorias = DB::table('categoria')
            ->select('idCat', 'nombre_categoria')
            ->whereIn('idCat', $catIds)
            ->get()
            ->keyBy('idCat');
        
        $subcategorias = DB::table('subcategoria')
            ->select('ID_sub', 'ID_cat', 'nombre_subcategoria')
            ->whereIn('ID_sub', $subIds)
            ->get()
            ->keyBy('ID_sub');
        
        $subcategorias2 = DB::table('subcategoria2')
            ->select('ID_sub2', 'ID_sub', 'nombre_subsubcategoria')
            ->whereIn('ID_sub2', $sub2Ids)
            ->get()
            ->keyBy('ID_sub2');
        
        // Construir el árbol
        $arbol = [];
        
        foreach ($categorias as $cat_id => $categoria) {
            $arbol[$cat_id] = [
                'id' => $cat_id,
                'nombre' => $categoria->nombre_categoria,
                'subcategorias' => []
            ];
        }
        
        foreach ($subcategorias as $sub_id => $subcategoria) {
            if (isset($arbol[$subcategoria->ID_cat])) {
                $arbol[$subcategoria->ID_cat]['subcategorias'][$sub_id] = [
                    'id' => $sub_id,
                    'nombre' => $subcategoria->nombre_subcategoria,
                    'subcategorias2' => []
                ];
            }
        }
        
        foreach ($subcategorias2 as $sub2_id => $subcategoria2) {
            $cat_id = $subcategorias[$subcategoria2->ID_sub]->ID_cat ?? null;
            
            if ($cat_id && isset($arbol[$cat_id]['subcategorias'][$subcategoria2->ID_sub])) {
                $arbol[$cat_id]['subcategorias'][$subcategoria2->ID_sub]['subcategorias2'][$sub2_id] = [
                    'id' => $sub2_id,
                    'nombre' => $subcategoria2->nombre_subsubcategoria
                ];
            }
        }
        
        // Convertir a array para la respuesta JSON
        $resultado = array_values($arbol);
        
        // Transformar las subcategorías de asociativas a indexadas
        foreach ($resultado as &$cat) {
            $cat['subcategorias'] = array_values($cat['subcategorias']);
            
            foreach ($cat['subcategorias'] as &$sub) {
                $sub['subcategorias2'] = array_values($sub['subcategorias2']);
            }
        }
        
        return response()->json($resultado);
    }
    
    
    
}
