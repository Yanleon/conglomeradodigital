<?php

use Illuminate\Support\Facades\Route;
use Bold\Http\Controllers\BoldController;

Route::group(['middleware' => ['web']], function () {
    Route::get('/bold/redirect', [BoldController::class, 'redirect'])->name('bold.redirect');
    Route::post('/bold/notify', [BoldController::class, 'notify'])->name('bold.notify');
});
