<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Configuracion_Controller extends Controller
{
    /**
     * Mostrar un listado de todas las configuraciones.
     */
    public function index()
    {
        $configuraciones = Configuracion::all();
        return view('configuraciones.index', compact('configuraciones'));
    }

    /**
     * Mostrar el formulario para crear una nueva configuración.
     */
    public function create()
    {
        return view('configuraciones.create');
    }

    /**
     * Almacenar una nueva configuración en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:configuracion,nombre',
            'valor' => 'nullable|string',
        ]);

        Configuracion::create($request->all());

        return redirect()->route('configuraciones.index')
            ->with('success', 'Configuración creada exitosamente.');
    }

    /**
     * Mostrar una configuración específica.
     */
    public function show(Configuracion $configuracion)
    {
        return view('configuraciones.show', compact('configuracion'));
    }

    /**
     * Mostrar el formulario para editar una configuración.
     */
    public function edit(Configuracion $configuracion)
    {
        return view('configuraciones.edit', compact('configuracion'));
    }

    /**
     * Actualizar una configuración específica en la base de datos.
     */
    public function update(Request $request, Configuracion $configuracion)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                Rule::unique('configuracion')->ignore($configuracion->id),
            ],
            'valor' => 'nullable|string',
        ]);

        $configuracion->update($request->all());

        return redirect()->route('configuraciones.index')
            ->with('success', 'Configuración actualizada exitosamente.');
    }

    /**
     * Eliminar una configuración específica de la base de datos.
     */
    public function destroy(Configuracion $configuracion)
    {
        $configuracion->delete();

        return redirect()->route('configuraciones.index')
            ->with('success', 'Configuración eliminada exitosamente.');
    }

    /**
     * Actualizar el número de productos por página.
     */
    public function updateProductosPagina(Request $request)
    {
        $request->validate([
            'valor' => 'required|integer|min:1|max:100',
        ]);

        $configuracion = Configuracion::where('nombre', 'productos_pagina')->first();
        
        if (!$configuracion) {
            return redirect()->back()->with('error', 'Configuración no encontrada');
        }

        $configuracion->valor = $request->valor;
        $configuracion->save();

        return redirect()->back()->with('success', 'Número de productos por página actualizado correctamente');
    }

    /**
     * Actualizar el color de fondo de la aplicación.
     */
    public function updateColorFondo(Request $request)
    {
        $request->validate([
            'valor' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
        ]);

        $configuracion = Configuracion::where('nombre', 'color_fondo')->first();
        
        if (!$configuracion) {
            return redirect()->back()->with('error', 'Configuración no encontrada');
        }

        $configuracion->valor = $request->valor;
        $configuracion->save();

        return redirect()->back()->with('success', 'Color de fondo actualizado correctamente');
    }

    /**
     * Obtener configuraciones para el frontend.
     */
    public function getConfiguraciones()
    {
        $configuraciones = Configuracion::all()->pluck('valor', 'nombre');
        return response()->json($configuraciones);
    }
}
