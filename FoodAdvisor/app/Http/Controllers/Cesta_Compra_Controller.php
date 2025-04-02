<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cesta_Compra;
use App\Models\Producto;


class Cesta_Compra_Controller extends Controller
{
    //Sobra
    public function getAll()
    {
        $cestas = Cesta_Compra::all();
        return response()->json($cestas,200);
    }

    //Revisar que el valor que devuelva, lo devuelva con los productos asociados cargados y valida que el id recibido exista
    public function getById(Cesta_Compra $id) {
        return $id;
    }

    //Sobra, integrar en getById
    public function getProdFromCesta(Cesta_Compra $cesta)
    {
        $cesta->load('productos');

        if(!$cesta){
            return response()->json(['mensaje'=>'Error: Cesta no encontrada', 404]);
        }
        return response()->json($cesta->productos,200);
    }

    //Revisar que se valide el ID_user que exista. Sol: required ya busca que la foreign key exista. No hay que cambiar
    public function store(Request $req) {
        $validator = Validator::make($req->all(), [
            'ID_user' => 'required|integer|min:1',
            'fecha_compra' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos de entrada no válidos', 'errors' => $validator->errors()], 422);
        }
        

        $cesta = Cesta_Compra::create([
            'ID_user' => $req['ID_user'],
            'fecha_compra' => $req['fecha_compra'],
        ]);

        if (!$cesta) {
            return response()->json(['error' => 'No se pudo crear la cesta de la compra'], 500);
        }

        return response()->json($cesta, 201);
    }

    //Revisar la funcionalidad de que si extiste añadir cantidad o devolver error de que el producto ya existe en canasta
    public function storeInCesta(Cesta_Compra $cesta, Request $req) {
        $cesta->load('productos');
        if(!$cesta){
            return response()->json(['mensaje'=>'Error: Cesta no encontrada'],404);
        }

        $validator = Validator::make($req->all(), [
            'ID_prod' => 'required|exists:producto,ID_prod',
            'cantidad' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos de entrada no válidos', 'errors' => $validator->errors()], 422);
        }

        $prod = Producto::find($req->ID_prod);

        $pivot = $cesta->productos()->wherePivot('ID_prod', $prod->ID_prod)->first();

        if($pivot) {
            $curr_cant = $pivot->pivot->cantidad;
            $new_cant = $curr_cant+$req->cantidad;
            $cesta->productos()->updateExistingPivot($prod->ID_prod, ['cantidad' => $new_cant]);
        } else {
            $cesta->productos()->attach($prod->ID_prod, ['cantidad' => $req->cantidad]);
        }

        $cesta->calcularPorcentajes();
        return response()->json(['mensaje' => 'producto añadido a la cesta correctamente'], 200);
    }

    //Mejor usar este.
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

    //Sobra
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

    public function deleteCesta(Cesta_Compra $cesta) {
        if (!$cesta) {
            return response()->json(['mensaje' => 'Error: Cesta no encontrada'], 404);
        }

        $cesta->delete();

        return response()->json(['mensaje' => 'Cesta eliminada correctamente'], 200);
    }

    public function removeProductoFromCesta(Cesta_Compra $cesta, $productoId) {
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

    //Revisar si se queda
    public function getHistorialCompras(Request $request)
    {
        $usuario = $request->user();
        $compras = Cesta_Compra::where('ID_user', $usuario->ID_user)->get();

        foreach ($compras as $compra) {
            $compra->load('productos');
        }

        return response()->json($compras, 200);
    }

    //Resumen
    //Falta getCestasByToken
}