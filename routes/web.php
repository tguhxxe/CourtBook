<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\CourtController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Payments\PaymentController;
use App\Models\Court;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('customer.home', ['courts' => Court::where('active', true)->orderBy('name')->take(3)->get(), 'settings' => Setting::findOrFail(1)]))->name('home');
Route::get('/courts', [CourtController::class, 'index'])->name('courts.index');
Route::get('/courts/{court}', [CourtController::class, 'show'])->name('courts.show');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'forgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendReset'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'updatePassword'])->middleware('throttle:6,1')->name('password.update');
});
Route::post('/midtrans/notification', [PaymentController::class, 'webhook'])->name('midtrans.webhook');
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/payments', [PaymentController::class, 'store'])->middleware('throttle:10,1')->name('payments.store');
    Route::post('/payments/{payment}/reconcile', [PaymentController::class, 'reconcile'])->middleware('throttle:30,1')->name('payments.reconcile');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
require __DIR__.'/admin.php';
