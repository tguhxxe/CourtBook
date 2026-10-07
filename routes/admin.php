<?php

use App\Http\Controllers\Admin\CourtController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OperationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/bookings', [DashboardController::class, 'bookings'])->name('bookings');
    Route::get('/transactions', [DashboardController::class, 'transactions'])->name('transactions');
    Route::resource('courts', CourtController::class)->except('show');
    Route::get('/maintenance', [OperationsController::class, 'maintenance'])->name('maintenance');
    Route::post('/maintenance', [OperationsController::class, 'storeMaintenance'])->name('maintenance.store');
    Route::delete('/maintenance/{maintenance}', [OperationsController::class, 'destroyMaintenance'])->name('maintenance.destroy');
    Route::get('/settings', [OperationsController::class, 'settings'])->name('settings');
    Route::put('/settings', [OperationsController::class, 'updateSettings'])->name('settings.update');
    Route::get('/refunds', [OperationsController::class, 'refunds'])->name('refunds');
    Route::put('/refunds/{refund}', [OperationsController::class, 'updateRefund'])->name('refunds.update');
});
