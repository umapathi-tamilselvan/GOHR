<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTaskController;

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

    // Project Management routes
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/assign-manager', [ProjectController::class, 'assignManager'])->name('projects.assign-manager');
    Route::post('projects/{project}/add-member', [ProjectController::class, 'addMember'])->name('projects.add-member');
    Route::delete('projects/{project}/remove-member/{user}', [ProjectController::class, 'removeMember'])->name('projects.remove-member');
    Route::resource('projects.tasks', ProjectTaskController::class);
    Route::patch('projects/{project}/tasks/{task}/status', [ProjectTaskController::class, 'updateStatus'])->name('projects.tasks.update-status');
    Route::patch('projects/{project}/tasks/{task}/assign', [ProjectTaskController::class, 'assign'])->name('projects.tasks.assign');
    Route::get('projects-report', [ProjectController::class, 'report'])->name('projects.report');
    Route::get('projects/{project}/tasks-report', [ProjectTaskController::class, 'report'])->name('projects.tasks.report');
});

require __DIR__.'/auth.php';
