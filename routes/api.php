<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('produtos', ProdutoController::class);
    // Route::apiResource('categorias', CategoriaController::class);
    Route::get('/listas', [CompraController::class, 'lista']);
    Route::get('/carrinho', [CompraController::class, 'carrinho']);
    Route::get('/historico', [CompraController::class, 'historico']);
    Route::get('/historico-produto', [CompraController::class, 'historicoComProduto']);
    Route::post('/compras', [CompraController::class, 'store']);
    Route::get('/compras/{compra}', [CompraController::class, 'show']);
    Route::put('/compras/{compra}', [CompraController::class, 'update']);
    Route::delete('/compras/{compra}', [CompraController::class, 'destroy']);
    Route::post('/carrinhos/{compra}/finalizar', [CompraController::class, 'finalizar']);
    Route::get('/historico', [CompraController::class, 'historico']);
});
