<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('users', function () {
    $user = Auth::user();

    // Check if the user is authenticated and has the permission 'view users'
    if ($user && $user->hasPermission('View Users')) {
        return view('users');
    }

    // Abort with a 403 error if the user does not have access
    abort(403, 'Unauthorized');
})->middleware(['auth'])->name('users');

Route::get('roles', function () {
    $user = Auth::user();

    // Check if the user is authenticated and has the permission 'view roles'
    if ($user && $user->hasPermission('View Roles')) {
        return view('roles');
    }

    // Abort with a 403 error if the user does not have access
    abort(403, 'Unauthorized');
})->middleware(['auth'])->name('roles');

Route::get('offices', function () {
    $user = Auth::user();

    // Check if the user is authenticated and has the permission 'view offices'
    if ($user && $user->hasPermission('View Offices')) {
        return view('offices');
    }

    // Abort with a 403 error if the user does not have access
    abort(403, 'Unauthorized');
})->middleware(['auth'])->name('offices');

Route::get('permissions', function () {
    if (Auth::user() && Auth::user()->hasPermission('View Permissions')) {
        return view('permissions');
    }

    // Abort with a 403 error if the user does not have access
    abort(403, 'Unauthorized');
})->middleware(['auth'])->name('permissions');

Route::get('attendances', function () {
    if (Auth::user() && Auth::user()->hasPermission('View All Attendances')) {
        return view('attendances');
    }

    abort(403, 'Unauthorized');
})->middleware(['auth'])->name('attendances');

Route::get('qr-code', function () {
    if (Auth::user() && (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Super Admin'))) {
        return view('qr-code');
    }

    abort(403, 'Unauthorized');
})->middleware(['auth'])->name('qr-code');

Route::middleware(['auth'])->group(function () {
    Route::post('/attendances/scan', [AttendanceController::class, 'processScan'])->name('attendances.scan'); // Add POST route
    Route::get('/attendances/scan', [AttendanceController::class, 'redirectToApi']); // Add GET route
    Route::view('scan', 'scan')->name('scan');
});

Route::get('leave-requests', function () {
    if (Auth::user() && Auth::user()->hasPermission('View Leave Requests')) {
        return view('leave-requests');
    }

    abort(403, 'Unauthorized');
})->middleware(['auth'])->name('leave-requests');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
