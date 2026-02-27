<?php

use App\Http\Controllers\ClinicaController;
use Illuminate\Support\Facades\Route;

// Ruta principal que carga el index
Route::get('/', [ClinicaController::class, 'index']);

// Ruta de contacto con nombre para que el botón funcione
Route::get('/contacto', [ClinicaController::class, 'contact'])->name('contacto');
