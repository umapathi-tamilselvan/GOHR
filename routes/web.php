<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditLogController;

// Vue.js SPA route - catch all routes and return the main app view
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// API routes will be handled by api.php
// The Vue.js router will handle all frontend routing

require __DIR__.'/auth.php';
