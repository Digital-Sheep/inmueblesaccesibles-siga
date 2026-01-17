<?php

use App\Http\Controllers\ContratoController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/contratos/apartado/{procesoVenta}', [ContratoController::class, 'generarApartado'])
    ->name('generar.contrato.apartado')
    ->middleware('auth');
