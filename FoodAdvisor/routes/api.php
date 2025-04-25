<?php

use App\Http\Controllers\Categoria_Controller;
use App\Http\Controllers\SubCategoria_Controller;
use App\Http\Controllers\SubCategoriax2_Controller;
use App\Http\Controllers\Supermercado_Controller;
use App\Http\Controllers\Cesta_Compra_Controller;
use App\Http\Controllers\Producto_temp_Controller;
use App\Http\Controllers\Producto_Controller;
use App\Http\Controllers\NivelPiramide_Controller;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EstadisticasProductosController;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtMiddleware;



Route::middleware([JwtMiddleware::class])->group(function () {
    
    Route::get('/usuario/cestas', [UsuarioController::class, 'getCestasUsuario']); //Info Especifica Producto, lista de cestas
    Route::get('/usuario', [UsuarioController::class, 'getByToken']);
    Route::get('/usuario/cestas/{id}',[Cesta_Compra_Controller::class, 'getById']);
    Route::delete('/usuario/delete', [UsuarioController::class, 'deleteUser']);


    Route::post('/cestas-compra/addProducto',[Cesta_Compra_Controller::class, 'storeInCesta']);
    Route::post('/cestas/recomendados', [Cesta_Compra_Controller::class, 'storeRecommendedInCesta']);
    Route::put('/cestas-compra/{cesta}/update-producto',[Cesta_Compra_Controller::class, 'updateProdFromCesta']);
    //Integrado en getById//Route::get('/cestas-compra/{cesta}/productos',[Cesta_Compra_Controller::class, 'getProdFromCesta']);
    Route::delete('/cestas-compra/{cesta}', [Cesta_Compra_Controller::class, 'deleteCesta']);


    Route::get('/cestas/{cesta}/recomendar', [Cesta_Compra_Controller::class, 'recomendacion']);

});


Route::get('productos', [Producto_Controller::class, 'getAll']); //Home
Route::get('productos/valores-max', [Producto_Controller::class, 'getMaxValues']); //Home

Route::get('productos/{id}', [Producto_Controller::class, 'getProducto']); //Info Específica Prod
Route::get('productos/{id}/super', [Producto_Controller::class, 'getNombreSupermercado']); //Info Específica Prod
Route::get('productos/productos-sim/{id}', [Producto_Controller::class, 'getSimilares']); //Info Específica Prod
Route::post('productos/filtrar', [Producto_Controller::class, 'filtrarProductos']); //Home
Route::post('productos', [Producto_Controller::class, 'postProducto']); //no comprobado


Route::get('/cestas-compra',[Cesta_Compra_Controller::class, 'getAll']);
Route::post('/cestas-compra', [Cesta_Compra_Controller::class, 'store']);
Route::put('/cestas-compra/{id}', [Cesta_Compra_Controller::class, 'updateCesta']); //Editar Cesta
Route::delete('/cestas-compra/{cesta}/delete-producto/{id}', [Cesta_Compra_Controller::class, 'removeProductoFromCesta']);
Route::middleware(['auth:sanctum'])->get('/compras/historial', [Cesta_Compra_Controller::class, 'getHistorialCompras']);

Route::get('/supermercados', [Supermercado_Controller::class, 'getAll']);
Route::get('/supermercados/{id}', [Supermercado_Controller::class, 'getById']);
Route::post('/supermercados', [Supermercado_Controller::class, 'create']);
Route::get('/supermercados/{id}/categorias-arbol', [Supermercado_Controller::class, 'getCategoriaArbol']);


Route::get('/categorias', [Categoria_Controller::class, 'getAll']);
Route::get('/categorias/{id}', [Categoria_Controller::class, 'getCategoria']);
Route::get('/subcategorias', [SubCategoria_Controller::class, 'getAll']);
Route::get('/subcategorias/{id}', [SubCategoria_Controller::class, 'getSubcategoria']);
Route::get('/subcategoriasx2', [SubCategoriax2_Controller::class, 'getAll']);
Route::get('/subcategoriasx2/{id}', [SubCategoriax2_Controller::class, 'getSubcategoriasx2']);
Route::get('/productos-temp', [Producto_temp_Controller::class, 'getAllProducts']);
Route::get('/productos-temp/{mes}', [Producto_temp_Controller::class, 'getProductsByMonth']); //Home

Route::get('/mostrar-niveles', [NivelPiramide_Controller::class, 'getNivelesPiramide']);
Route::get('/nivel-productos/{id}', [NivelPiramide_Controller::class, 'getPiramide']); //Home. Este filtro irá por separado.

Route::get('/usuario/get-top-sellers', [Producto_Controller::class, 'getTopSellers']);
Route::get('/usuario/{id}/rol', [UsuarioController::class, 'getRol']);
Route::post('/usuario', [UsuarioController::class, 'putUser']);
Route::post('/usuario/register', [UsuarioController::class, 'register']);
Route::post('/usuario/login', [UsuarioController::class, 'login']);
Route::get('/usuario/login/{id}', [UsuarioController::class, 'getUser']);


//Estadisticas
Route::get('/estadisticas/productos', [EstadisticasProductosController::class, 'obtenerEstadisticas']);
Route::get('/estadisticas/compras', [EstadisticasProductosController::class, 'obtenerEstadisticasCestas']);

//Recomendar productos

Route::get('productos-temp/{idTemp}/detalles', [Producto_temp_Controller::class, 'getDetalles']);
Route::get('productos-temp', [Producto_temp_Controller::class, 'getProductosDelMes']);
Route::get('productos-temp/{mes}', [Producto_temp_Controller::class, 'getProductsByMonth']);

Route::get('/cestas/{id}/porcentajes', [Cesta_Compra_Controller::class, 'obtenerPorcentajesCesta']);