<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\UserController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/', [OrderController::class, 'index'])->name('home');
    
    Route::get('/agendamiento', function () {
        return view('agendamiento.index');
    })->name('agendamiento.index');

    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    
    Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios.index');
    
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');
    
    Route::get('/informes', [InformeController::class, 'index'])->name('informes.index');
});
