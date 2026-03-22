<?php

use App\Http\Controllers\PasarelaController;

Route::get('/boton-pago', [PasarelaController::class, 'mostrarBoton'])->name('boton.pago');
Route::post('/generar-hash', [PasarelaController::class, 'generarHash'])->name('generar.hash');
