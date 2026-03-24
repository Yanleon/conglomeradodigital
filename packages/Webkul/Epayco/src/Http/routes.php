<?php

use Illuminate\Support\Facades\Route;

use Webkul\Epayco\Http\Controllers\EpaycoController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('epayco/standard')->group(function () {
        Route::get('/set-order', [EpaycoController::class, 'setOrder'])->name('epayco.standard.set-order');
        Route::get('/create-order', [EpaycoController::class, 'createOrder'])->name('epayco.standard.create-order');
        //-- pagina de respuesta de epayco
        Route::get('/success', [EpaycoController::class, 'success'])->name('epayco.standard.success');

        Route::post('/ipn', [EpaycoController::class, 'ipn'])
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->name('epayco.standard.ipn');
    });
});

