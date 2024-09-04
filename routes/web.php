<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('permissions', function () {
    if (Auth::user() && Auth::user()->hasPermission('View Permissions')) {
        return view('permissions');
    }

    // Abort with a 403 error if the user does not have access
    abort(403, 'Unauthorized');
})->middleware(['auth'])->name('permissions');

Route::view('table1', 'table1')
    ->middleware(['auth'])
    ->name('table1');


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
