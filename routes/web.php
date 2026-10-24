<?php

use App\Http\Controllers\ShoppingListController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', [ShoppingListController::class, 'index'])->name('dashboard');
    Route::post('/items', [ShoppingListController::class, 'store'])->name('items.store');
    Route::patch('/items/{item}', [ShoppingListController::class, 'toggle'])->name('items.toggle');
    Route::delete('/items/{item}', [ShoppingListController::class, 'destroy'])->name('items.destroy');
    Route::patch('/items/{item}/move', [ShoppingListController::class, 'move'])->name('items.move');
    Route::patch('/spending-limit', [ShoppingListController::class, 'updateLimit'])->name('spending-limit.update');
    Route::post('/share', [ShoppingListController::class, 'share'])->name('share');
});
