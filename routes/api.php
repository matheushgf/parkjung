<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifyCsrfToken;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(App\Http\Controllers\GruposController::class)
    ->prefix('grupos')
    ->name('grupos.')
    ->group(function () {
        Route::get('/getusers', 'getUsers')->name('api.getUsers')->middleware(['auth:sanctum', 'ability:grupos-getUsers']);
    });

Route::controller(App\Http\Controllers\ReceitasController::class)
    ->prefix('receitas')
    ->name('receitas.')
    ->group(function () {
        Route::get('/getreceitas', 'getReceitas')->name('api.getReceitas')->middleware(['auth:sanctum', 'ability:receitas-getIngredientes']);
    });

Route::controller(App\Http\Controllers\ProdutosController::class)
    ->prefix('produtos')
    ->name('produtos.')
    ->group(function () {
        Route::get('/getprodutos', 'getProdutos')->name('api.getProdutos')->middleware(['auth:sanctum', 'ability:receitas-getIngredientes']);
    });