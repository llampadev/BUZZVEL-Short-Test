<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\PaymentRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [PaymentRequestController::class, 'index'])->name('dashboard');
    Route::get('/payment-requests/create', [PaymentRequestController::class, 'create'])->name('payment-requests.create');
    Route::post('/payment-requests', [PaymentRequestController::class, 'store'])->name('payment-requests.store');
    Route::get('/payment-requests/{paymentRequest}', [PaymentRequestController::class, 'show'])->name('payment-requests.show');
    Route::patch('/payment-requests/{paymentRequest}/approve', [PaymentRequestController::class, 'approve'])->name('payment-requests.approve');
    Route::patch('/payment-requests/{paymentRequest}/reject', [PaymentRequestController::class, 'reject'])->name('payment-requests.reject');
});
