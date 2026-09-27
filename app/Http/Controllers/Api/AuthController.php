<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string', // Si se loguean con usuario (no email)
            'password' => 'required|string',
            'device_name' => 'nullable|string', // Permite recibir el nombre del dispositivo
        ]);

        $user = User::where('name', $request->name)->first();

        // Verificar credenciales
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'name' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Si no se envía device_name desde la app, asigna un valor genérico
        $deviceName = $request->device_name ?? 'mobile-app';

        // Crear Token de acceso en la tabla personal_access_tokens
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Inicio de sesión exitoso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'rol' => $user->getRoleNames()->first() ?? 'Operador',
                'operador_id' => $user->operador->id ?? null,
            ],
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        // Revocar solo el token actual con el que se identificó el móvil
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sesión cerrada correctamente',
        ]);
    }
}
