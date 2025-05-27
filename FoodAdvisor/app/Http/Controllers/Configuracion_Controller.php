<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class Configuracion_Controller extends Controller
{
    public function index()
    {
        return response()->json(Configuracion::all());
    }

    public function create()
    {
        return response()->json(['message' => 'Formulario no disponible en API.']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|unique:configuracion,nombre',
            'valor' => 'nullable|string',
        ]);

        $config = Configuracion::create($validated);

        return response()->json([
            'message' => 'Configuración creada exitosamente.',
            'data' => $config
        ], 201);
    }

    public function show(Configuracion $configuracion)
    {
        return response()->json($configuracion);
    }

    public function edit(Configuracion $configuracion)
    {
        return response()->json(['message' => 'Formulario no disponible en API.']);
    }

    public function update(Request $request, Configuracion $configuracion)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                Rule::unique('configuracion')->ignore($configuracion->id),
            ],
            'valor' => 'nullable|string',
        ]);

        $configuracion->update($validated);

        return response()->json([
            'message' => 'Configuración actualizada exitosamente.',
            'data' => $configuracion
        ]);
    }

    public function destroy(Configuracion $configuracion)
    {
        $configuracion->delete();

        return response()->json(['message' => 'Configuración eliminada exitosamente.']);
    }

    public function getProductosPagina()
    {
        $configuracion = Configuracion::where('nombre', 'productos_pagina')->first();

        if (!$configuracion) {
            return response()->json(['error' => 'Configuración no encontrada'], 404);
        }

        return response()->json([
            'nombre' => $configuracion->nombre,
            'valor' => (int) $configuracion->valor
        ]);
    }

    public function getNumProductosPagina()
    {
        $valor = Configuracion::where('nombre', 'productos_pagina')->value('valor');
        return response()->json(['valor' => $valor]);
    }

    public function getColorHeader()
    {
        $configuracion = Configuracion::where('nombre', 'color_header')->first();

        if (!$configuracion) {
            return response()->json(['error' => 'Configuración no encontrada'], 404);
        }

        return response()->json([
            'nombre' => $configuracion->nombre,
            'valor' => $configuracion->valor
        ]);
    }

    public function updateProductosPagina(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        if ($usuario->rol !== 'admin') {
            return response()->json(['error' => 'No tienes permisos para realizar esta acción'], 403);
        }

        $request->validate([
            'valor' => 'required|integer|min:1|max:100',
        ]);

        $configuracion = Configuracion::where('nombre', 'productos_pagina')->first();

        if (!$configuracion) {
            return response()->json(['error' => 'Configuración no encontrada'], 404);
        }

        $configuracion->valor = $request->valor;
        $configuracion->save();

        return response()->json(['success' => 'Número de productos por página actualizado correctamente']);
    }

    public function updateColorHeader(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        if ($usuario->rol !== 'admin') {
            return response()->json(['error' => 'No tienes permisos para realizar esta acción'], 403);
        }

        $request->validate([
            'valor' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
        ]);

        $configuracion = Configuracion::where('nombre', 'color_header')->first();

        if (!$configuracion) {
            return response()->json(['error' => 'Configuración no encontrada'], 404);
        }

        $configuracion->valor = $request->valor;
        $configuracion->save();

        return response()->json(['success' => 'Color de header actualizado correctamente']);
    }

    public function getConfiguraciones()
    {
        $configuraciones = Configuracion::all();
        return response()->json($configuraciones);
    }
}
