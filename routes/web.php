<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditLogController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', UserController::class);

    // Attendance routes - full resource routes plus custom routes
    Route::resource('attendances', AttendanceController::class);
    Route::get('attendances-list', [AttendanceController::class, 'list'])->name('attendances.list');
    Route::get('attendances-report', [AttendanceController::class, 'report'])->name('attendances.report');
    Route::get('attendances-manage', [AttendanceController::class, 'manage'])->name('attendances.manage');
    Route::post('attendances-manual', [AttendanceController::class, 'storeManual'])->name('attendances.storeManual');

    Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
});

require __DIR__.'/auth.php';
