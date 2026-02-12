<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ExternalLogin;
use App\Livewire\ExternalDashboard;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

// Google Authentication Routes
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// External Dashboard Routes
Route::prefix('external')->name('external.')->group(function () {
    Route::get('/{token}', ExternalLogin::class)->name('login');
    Route::get('/{token}/dashboard', ExternalDashboard::class)->name('dashboard');
});

// Error Pages Preview (dev only)
Route::prefix('error-preview')->group(function () {
    Route::get('/', function () {
        $errors = [403, 404, 419, 500, 503];
        return view('errors.preview', compact('errors'));
    })->name('error.preview');

    Route::get('/{code}', function ($code) {
        $validCodes = [403, 404, 419, 500, 503];
        if (!in_array((int) $code, $validCodes)) {
            abort(404);
        }
        return response()->view("errors.{$code}", [], (int) $code);
    })->name('error.show');
});
