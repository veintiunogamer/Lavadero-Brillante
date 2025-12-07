<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;

# Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    # Ruta principal
    Route::get('/', [OrderController::class, 'index'])->name('home');
    
    # Rutas para la gestión de agendamientos
    Route::get('/orders', [OrderController::class, 'agendamiento'])->name('orders.index');
    Route::get('/orders/status/{status}', [OrderController::class, 'getByStatus'])->name('orders.getByStatus');

    # Rutas para la gestión de clientes
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    
    # Rutas para la gestión de servicios
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    
    # Rutas para la gestión de usuarios (Solo Administradores)
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/update/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/delete/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    
    # Rutas para la gestión de informes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    
});
