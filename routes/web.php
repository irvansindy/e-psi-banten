<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
Route::get('/', function () {
    return view('welcome');
});
Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware(['guest'])->name('login');

Route::get('/dashboard', function() {
    return view('admin.dashboard');
});