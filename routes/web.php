<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\UserController;

# Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    # Ruta principal
    Route::get('/', [OrderController::class, 'index'])->name('home');
    
    # Rutas para la gestión de agendamientos
    Route::get('/agendamiento', function () {
        return view('agendamiento.index');
    })->name('agendamiento.index');

    # Rutas para la gestión de clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    
    # Rutas para la gestión de servicios
    Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios.index');
    
    # Rutas para la gestión de usuarios
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios/store', [UserController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/update/{id}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/delete/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');
    
    # Rutas para la gestión de informes
    Route::get('/informes', [InformeController::class, 'index'])->name('informes.index');
    
});
