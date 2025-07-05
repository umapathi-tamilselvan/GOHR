<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', UserController::class);

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendances', [AttendanceController::class, 'list'])->name('attendances.list');
    Route::get('/attendance/manage', [AttendanceController::class, 'manage'])->name('attendance.manage');
    Route::post('/attendance/manage', [AttendanceController::class, 'storeManual'])->name('attendance.store.manual');
    Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/audit-log', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-log.index');
    });
});

require __DIR__.'/auth.php';
