<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Usuario;
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

    public function redirectToGoogle()
    {
        // O método stateless() é crucial para APIs, pois não utiliza sessões.
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Procura pelo usuário ou cria um novo se não existir
            // updateOrCreate é perfeito para isso.
            $user = User::updateOrCreate(
                ['google_id' => $googleUser->id],
                [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    // Usuários sociais não precisam de senha local
                    'password' => Hash::make(Str::random(24))
                ]
            );

            // Cria o token de acesso para o usuário
            $token = $user->createToken('auth-token')->plainTextToken;

            // Retorna o usuário e o token para o frontend
            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            // Em caso de erro, retorna uma mensagem
            return response()->json(['error' => 'Não foi possível autenticar com o Google.'], 401);
        }
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
