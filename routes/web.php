<?php

use App\Http\Controllers\SsoController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/sso/redirect', [SsoController::class, 'redirect'])->name('sso.redirect');
Route::get('/auth/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');

Route::get('/', function () {
    return redirect('/admin/login');
});
