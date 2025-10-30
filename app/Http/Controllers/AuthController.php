<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $usuarioCriado = User::where('email', $request->email)->first();

        if ($usuarioCriado) {
            throw ValidationException::withMessages([
                'email' => 'O email ja foi cadastrado.'
            ]);
        }

        $usuario = User::create([
            'name'  => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Cria token
        $token = $usuario->createToken('token')->plainTextToken;

        return response()->json([
            'usuario' => $usuario,
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = User::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais estão incorretas.'],
            ]);
        }

        // Gera token de acesso (válido por, por exemplo, 30 minutos)
        $accessToken = $usuario->createToken(
            'access_token',
            [],
            now()->addMinutes(30) // expira em 30 minutos
        );
        $accessToken->accessToken->expires_at = now()->addMinutes(30);
        $accessToken->accessToken->save();

        // Gera refresh token (válido por 7 dias, sem escopo)
        $refreshToken = $usuario->createToken(
            'refresh_token',
            [],
            now()->addDays(7)
        );
        $refreshToken->accessToken->expires_at = now()->addDays(7);
        $refreshToken->accessToken->save();

        return response()->json([
            'usuario' => $usuario,
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    public function refreshToken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $tokenId = $request->refresh_token; // Supondo que você está enviando o ID do token
        $token = PersonalAccessToken::find($tokenId);

        $token = $request->user() ? $request->user()->currentAccessToken() : null;

        if (!$token) {
            return response()->json(['erro' => 'Token inválido ou expirado.'], 401);
        }

        $usuario = $token->tokenable;

        // Gera novo access token
        $accessToken = $usuario->createToken('access_token_' . uniqid());
        $accessToken->accessToken->tipo = 'access';
        $accessToken->accessToken->expires_at = now()->addMinutes(30);
        $accessToken->accessToken->save();

        // Gera novo refresh token
        $refreshToken = $usuario->createToken('refresh_token_' . uniqid());
        $refreshToken->accessToken->tipo = 'refresh';
        $refreshToken->accessToken->expires_at = now()->addDays(30);
        $refreshToken->accessToken->save();

        return response()->json([
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken
        ]);
    }
}
