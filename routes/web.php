<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OracleController;

Route::get('/', function () {
    return redirect()->route('oracle.login');
});

// Rutas de autenticación
Route::get('/login', [OracleController::class, 'login'])->name('oracle.login');
Route::post('/authenticate', [OracleController::class, 'authenticate'])->name('oracle.authenticate');
Route::post('/logout', [OracleController::class, 'logout'])->name('oracle.logout');

// Rutas protegidas (requieren autenticación)
Route::middleware(['web'])->group(function () {
    Route::get('/oracle', [OracleController::class, 'index'])->name('oracle.index');
    Route::post('/oracle/actualizar', [OracleController::class, 'actualizarTabla'])->name('oracle.actualizar');
    Route::post('/oracle/merge', [OracleController::class, 'procesarMerge'])->name('oracle.merge');
    Route::post('/oracle/crear-procedimiento', [OracleController::class, 'crearProcedimiento'])->name('oracle.crear-procedimiento');
});

// Verificar autenticación simple
Route::middleware(['web'])->group(function () {
    Route::get('/oracle', function () {
        if (!session('authenticated')) {
            return redirect()->route('oracle.login')->with('error', 'Debes iniciar sesión primero');
        }
        return app(OracleController::class)->index();
    })->name('oracle.index');
    
    Route::post('/oracle/actualizar', function (Illuminate\Http\Request $request) {
        if (!session('authenticated')) {
            return redirect()->route('oracle.login');
        }
        return app(OracleController::class)->actualizarTabla($request);
    })->name('oracle.actualizar');
    
    Route::post('/oracle/merge', function () {
        if (!session('authenticated')) {
            return redirect()->route('oracle.login');
        }
        return app(OracleController::class)->procesarMerge();
    })->name('oracle.merge');
    
    Route::post('/oracle/crear-procedimiento', function () {
    if (!session('authenticated')) {
        return redirect()->route('oracle.login');
    }
    return app(OracleController::class)->crearProcedimiento();
})->name('oracle.crear-procedimiento');
});