<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VehicleTypeController;

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
        Route::put('/users/activate/{id}', [UserController::class, 'activate'])->name('users.activate');
    });
    
    # Rutas para el perfil del usuario
    Route::get('/profile', [UserController::class, 'profile'])->name('profile.index');
    Route::put('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
    
    # Rutas para la gestión de informes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    
    # Rutas para configuraciones
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');
    
    # Rutas API para categorías
    Route::resource('categories', CategoryController::class)->except(['create', 'edit']);
    
    # Rutas API para tipos de vehículo
    Route::resource('vehicle-types', VehicleTypeController::class)->except(['create', 'edit']);
    Route::put('/vehicle-types/activate/{id}', [VehicleTypeController::class, 'activate'])->name('vehicle-types.activate');
    
    # Rutas API para clientes
    Route::get('/api/clients', [ClientController::class, 'apiIndex'])->name('clients.api.index');
    Route::resource('clients', ClientController::class)->except(['index', 'create', 'edit']);
    Route::put('/clients/activate/{id}', [ClientController::class, 'activate'])->name('clients.activate');
    
    # Rutas API para servicios
    Route::get('/api/services', [ServiceController::class, 'apiIndex'])->name('services.api.index');
    Route::resource('services', ServiceController::class)->except(['index', 'create', 'edit']);
    Route::put('/services/activate/{id}', [ServiceController::class, 'activate'])->name('services.activate');
    
});
