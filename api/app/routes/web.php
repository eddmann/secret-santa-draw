<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BootstrapController;
use App\Http\Controllers\DrawController;
use App\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Route;

Route::get('/api/bootstrap', [BootstrapController::class, 'index'])
    ->name('bootstrap');

Route::controller(AuthenticationController::class)->group(function () {
    Route::post('/api/register', 'register')->name('register');
    Route::post('/api/login', 'login')->name('login');
    Route::post('/api/logout', 'logout')->name('logout');
    Route::get('/api/whoami', 'whoami')
        ->middleware('auth:sanctum')
        ->name('whoami');
});

Route::controller(GroupController::class)->group(function () {
    Route::get('/api/groups', 'index')->name('groups');
    Route::post('/api/groups', 'create')->name('groups.create');
    Route::get('/api/groups/{group}', 'show')->name('groups.show');
    Route::put('/api/groups/{group}', 'update')->name('groups.update');
})->middleware('auth:sanctum');

Route::controller(DrawController::class)->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/api/groups/{group}/draws', 'index')->name('draws');
        Route::post('/api/groups/{group}/draws', 'create')->name('draws.create');
        Route::delete('/api/groups/{group}/draws/{draw}', 'delete')->name('draws.delete');
    });
    Route::get('/api/groups/{group}/draws/{draw}', 'show')->name('draws.show');
});

Route::controller(AllocationController::class)->group(function () {
    Route::get('/api/groups/{group}/draws/{draw}/allocations', 'index')
        ->middleware('auth:sanctum')
        ->name('allocations');
    Route::get('/api/groups/{group}/draws/{draw}/allocations/{allocation}', 'show')->name('allocations.show');
    Route::put('/api/groups/{group}/draws/{draw}/allocations/{allocation}/ideas', 'provideIdeas')->name('allocations.ideas');
});
