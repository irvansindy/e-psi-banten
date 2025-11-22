<?php

use Illuminate\Support\Facades\Route;

// Route Login (Guest only)
Route::get('/', function () {
    return view('welcome');
})->middleware('guest')->name('login');

// Route Dashboard (Authenticated users only)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function() {
        return view('admin.dashboard');
    })->name('dashboard');
    Route::get('');
});

// Logout route (optional, jika belum ada)
Route::post('/logout', function() {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');