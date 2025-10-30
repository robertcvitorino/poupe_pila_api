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
    public function register(): void{
           $this->renderable(function (AuthenticationException $e, $request) {

        return response()->json([
            'error' => 'unauthenticated',
            'message' => 'Token inválido ou expirado. Faça login novamente.',
        ], 401);
    });

    }
}
