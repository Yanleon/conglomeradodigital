<?php

use Illuminate\Support\Facades\Route;
use Webkul\BoldPayment\Http\Controllers\PaymentController;

Route::group(['middleware' => ['web', 'locale', 'theme', 'currency']], function () {
    Route::get('/bold/checkout', [PaymentController::class, 'checkout'])->name('bold.checkout');
    Route::get('/bold/callback', [PaymentController::class, 'callback'])->name('bold.callback');
    Route::post('/bold/generate-signature', [PaymentController::class, 'generateSignature'])->name('bold.generate.signature');
});
