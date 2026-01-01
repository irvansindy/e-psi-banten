<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PsychologyTestController;

// Route Login (Guest only)
Route::get('/', function () {
    return view('welcome');
})->middleware('guest')->name('login');

// Route Dashboard (Authenticated users only)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function() {
        return view('admin.dashboard');
    })->name('dashboard');
    Route::get('psychology-tests', [PsychologyTestController::class, 'index'])->name('psychology-tests.index');
    Route::get('psychology-tests/dropdown-data', [PsychologyTestController::class, 'getDropdownData'])->name('psychology-tests.dropdown-data');
    Route::get('psychology-tests/data', [PsychologyTestController::class, 'getData'])->name('psychology-tests.data');
    Route::post('psychology-tests', [PsychologyTestController::class, 'store'])->name('psychology-tests.store');
    Route::get('psychology-tests/{id}', [PsychologyTestController::class, 'show'])->name('psychology-tests.show');
    Route::get('psychology-tests/{id}/pdf', [PsychologyTestController::class, 'generatePDF'])->name('psychology-tests.pdf');
    Route::put('psychology-tests/{id}', [PsychologyTestController::class, 'update'])->name('psychology-tests.update');
    Route::delete('psychology-tests/{id}', [PsychologyTestController::class, 'destroy'])->name('psychology-tests.destroy');
});

// Logout route (optional, jika belum ada)
Route::post('/logout', function() {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');