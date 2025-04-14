<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cesta_Compra;
use App\Models\Producto;


class Cesta_Compra_Controller extends Controller
{
    public function getAll()
    {
        $cestas = Cesta_Compra::all();
        return response()->json($cestas,200);
    }

    public function getById(Cesta_Compra $id)
    {
        return $id;
    }

    //sobra. Integrar en getById
    public function getProdFromCesta(Cesta_Compra $cesta)
    {
        $cesta->load('productos');

        if(!$cesta){
            return response()->json(['mensaje'=>'Error: Cesta no encontrada', 404]);
        }
        return response()->json($cesta->productos,200);
    }


    //por arreglar validación
    public function store(Request $req)
    {
        $cestaValidada = $req->validate([
            'ID_user' => 'required|integer|min:1',
            'fecha_compra' => 'required|date'
        ]);
        
        $cesta = Cesta_Compra::create($cestaValidada);

        if (!$cesta) {
            return response()->json(['error' => 'No se pudo crear la cesta de la compra'], 500);
        }

        return response()->json($cesta, 201);
    }

    public function storeInCesta(Cesta_Compra $cesta, Request $req)
    {
        $cesta->load('productos');
        if(!$cesta){
            return response()->json(['mensaje'=>'Error: Cesta no encontrada'],404);
        }
        
        $validatedProd = $req->validate([
            'ID_prod' => 'required|exists:producto,ID_prod',
            'cantidad' => 'required|integer',
        ]);

        $prod = Producto::find($validatedProd['ID_prod']);

        $pivot = $cesta->productos()->wherePivot('ID_prod', $prod->ID_prod)->first();

        if($pivot){
            $curr_cant = $pivot->pivot->cantidad;
            $new_cant = $curr_cant+$validatedProd['cantidad'];
            $cesta->productos()->updateExistingPivot($prod->ID_prod, ['cantidad' => $new_cant]);
        }else{
            $cesta->productos()->attach($prod->ID_prod, ['cantidad' => $validatedProd['cantidad']]);
        }

        $cesta->calcularPorcentajes();
        return response()->json(['mensaje' => 'producto añadido a la cesta correctamente'], 200);
    }

    public function updateProdFromCesta(Cesta_Compra $cesta, Request $req)
    {
        $cesta->load('productos');
        if(!$cesta){
            return response()->json(['mensaje'=>'Error: Cesta no encontrada'], 404);
        }
        
        $validatedProd = $req->validate([
            'ID_prod' => 'required|exists:producto,ID_prod',
            'cantidad' => 'required|integer|min:1',
        ]);

        $prod = Producto::find($validatedProd['ID_prod']);

        if (!$prod) {
            return response()->json(['mensaje' => 'Error: Producto no encontrado'], 404);
        }

        $pivotData = $cesta->productos()->wherePivot('ID_prod', $prod->ID_prod)->first();

        $cesta->calcularPorcentajes();

        if ($pivotData) {
            $cesta->productos()->updateExistingPivot($prod->ID_prod, ['cantidad' => $validatedProd['cantidad']]);
            $cesta->productos()->syncWithoutDetaching([
                $prod->ID_prod => ['cantidad' => $validatedProd['cantidad']]
            ]);
            return response()->json(['mensaje' => 'Cantidad actualizada correctamente'], 200);

        } else {
            return response()->json(['mensaje' => 'El producto no está en la cesta'], 404);
        }
    }

    public function updateCesta(Request $req, $id)
    {
        $cesta = Cesta_compra::find($id);
        if(!$cesta){
            return response()->json(["mensaje" => "Error: Cesta no encontrada", 404]);
        }
        $cestaValidada = $req->validate([
            'ID_user' => 'required|integer|min:1',
            'fecha_compra' => 'required|date'
        ]);

        $cesta->update($cestaValidada);
        return response()->json(["mensaje" => "Cesta actualizada"], 200);
    }


    public function deleteCesta(Cesta_Compra $cesta)
    {
        if (!$cesta) {
            return response()->json(['mensaje' => 'Error: Cesta no encontrada'], 404);
        }

        $cesta->delete();

        return response()->json(['mensaje' => 'Cesta eliminada correctamente'], 200);
    }


    public function removeProductoFromCesta(Cesta_Compra $cesta, $productoId)
    {
        if (!$cesta) {
            return response()->json(['mensaje' => 'Error: Cesta no encontrada'], 404);
        }

        $productInCesta = $cesta->productos()->wherePivot('ID_prod', $productoId)->first();

        if (!$productInCesta) {
            return response()->json(['mensaje' => 'Error: Producto no encontrado en la cesta'], 404);
        }

        $cesta->productos()->detach($productoId);

        return response()->json(['mensaje' => 'producto eliminado correctamente de la cesta'], 200);
    }

    public function getHistorialCompras(Request $request)
{
    $usuario = $request->user();
    $compras = Cesta_Compra::where('ID_user', $usuario->ID_user)->get();

    foreach ($compras as $compra) {
        $compra->load('productos');
    }

    return response()->json($compras, 200);
}


}
