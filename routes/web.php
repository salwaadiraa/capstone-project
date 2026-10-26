<?php

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AppController::class, 'showLogin'])->name('login');
    Route::post('/login', [AppController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AppController::class, 'dashboard']);
    Route::get('/tickets', [AppController::class, 'tickets']);
    Route::get('/reports', [AppController::class, 'reports']);
    
    Route::post('/tickets', [AppController::class, 'storeTicket']);
    Route::post('/tickets/{ticket}/approve', [AppController::class, 'approveTicket']);
    
    Route::post('/profile', [AppController::class, 'updateProfile']);
    Route::post('/password', [AppController::class, 'updatePassword']);
    Route::post('/logout', [AppController::class, 'logout']);

    // Tambahan Route Export, Revisi, dan Manajemen Pegawai (Admin)
    Route::get('/reports/export', [AppController::class, 'exportReports']);
    Route::post('/tickets/{ticket}/revise', [AppController::class, 'reviseTicket']);
    
    Route::get('/users', [AppController::class, 'users']);
    Route::post('/users', [AppController::class, 'storeUser']);
    Route::post('/users/{user}/delete', [AppController::class, 'deleteUser']);

    // Tambahkan di dalam Route::middleware('auth')->group:
    Route::get('/ticket-reports', [AppController::class, 'ticketReports']);
    Route::get('/ticket-reports/export', [AppController::class, 'exportTicketReports']);

    Route::post('/ap-methods', [AppController::class, 'storeApMethod']);
    Route::post('/ap-methods/{apMethod}/delete', [AppController::class, 'deleteApMethod']);
    Route::post('/kendaraans', [AppController::class, 'storeKendaraan']);
    Route::post('/kendaraans/{kendaraan}/delete', [AppController::class, 'deleteKendaraan']);
});