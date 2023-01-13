<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home');

Route::controller(App\Http\Controllers\ProdutosController::class)
    ->prefix('produtos')
    ->name('produtos.')
    ->group(function () {
        Route::get('/', 'index')->name('list');
        Route::get('/novo', 'new')->name('new');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{produto}', 'edit')->name('edit');
        Route::put('/update/{produto}', 'update')->name('update');
        Route::delete('/delete/{produto}', 'delete')->name('delete');
        Route::put('/restore/{produto}', 'restore')->name('restore');
    });