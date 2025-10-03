<?php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Uma lista dos tipos de exceção que não devem ser reportados.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * Uma lista dos inputs que nunca devem ser exibidos em validação.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Registre callbacks para tratamento de exceções.
     */
    public function register(): void
    {

    }

    /**
     * Lida com requests não autenticadas em APIs RESTful.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Se a requisição espera JSON (API REST), retorna erro 401 em JSON
        if ($request->expectsJson()) {
            return response()->json([
                'erro' => 'Não autenticado ou token inválido.'
            ], 401);
        }

        // Para web (Blade, etc), redireciona normalmente
        return redirect()->guest(route('login'));
    }
}
