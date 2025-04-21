<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Cesta_Compra;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


class UsuarioController extends Controller
{
    public function getRol($id)
    {
        $usuario = Usuario::find($id);
    
        if (!$usuario) {
            return response()->json(['error' => 'usuario no encontrado'], 404);
        }
    
        return response()->json(['rol' => $usuario->rol], 200);
    }

    public function getByToken()
    {
        $usuario = JWTAuth::parseToken()->authenticate();
    
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 404);
        }
    
        return response()->json(['usuario' => $usuario], 200);
    }

    public function putUser(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'correo' => 'required|email|unique:usuario,correo',
            'contrasenia' => 'required|string|min:6',
            'rol' => 'required|string',
        ]);

        $usuario = Usuario::create([
            'nombre' => $validatedData['nombre'],
            'apellidos' => $validatedData['apellidos'],
            'correo' => $validatedData['correo'],
            'contrasenia' => Hash::make($validatedData['contrasenia']),
            'rol' => $validatedData['rol'],
        ]);

        return response()->json(['mensaje' => 'usuario creado correctamente', 'usuario' => $usuario], 201);
    }

    public function deleteUser()
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
        
        $usuario->delete();
        
        return response()->json(['mensaje' => 'usuario eliminado correctamente'], 200);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'correo' => 'required|email|unique:usuario,correo',
            'contrasenia' => 'required|string|min:6',
            'rol' => 'required|string',
        ]);

        $usuario = Usuario::create([
            'nombre' => $validatedData['nombre'],
            'apellidos' => $validatedData['apellidos'],
            'correo' => $validatedData['correo'],
            'contrasenia' => Hash::make($validatedData['contrasenia']), 
            'rol' => $validatedData['rol'],
        ]);

        return response()->json([
            'mensaje' => 'usuario registrado correctamente',
            'usuario' => $usuario
        ], 201);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'correo' => 'required|email',
            'contrasenia' => 'required|string|min:6',
        ]);

        $usuario = Usuario::where('correo', $validatedData['correo'])->first();

        if (!$usuario || !Hash::check($validatedData['contrasenia'], $usuario->contrasenia)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $token = JWTAuth::fromUser($usuario);

        return response()->json([
            'mensaje' => 'Inicio de sesión exitoso',
            'usuario' => [
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
            ],
            'token' => $token,
        ], 200);
    }

    public function getUser($id)
    {
        $usuario = Usuario::find($id);
        return response()->json($usuario, 200);
    }

    public function getCestasUsuario()
    {
        $usuario = JWTAuth::parseToken()->authenticate();
        
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 404);
        }
        
        $cestas = \App\Models\Cesta_Compra::where('ID_user', $usuario->ID_user)
        ->whereNull('deleted_at')
        ->get();
        
        return response()->json(['cestas' => $cestas], 200);
    }
}
