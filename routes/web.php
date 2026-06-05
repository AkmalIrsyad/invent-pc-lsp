<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Persediaan (Inventory)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');

    // Kategori
    Route::resource('categories', CategoryController::class)->except(['create', 'edit']);

    // Barang (Products)
    Route::resource('products', ProductController::class)->except(['create', 'edit', 'destroy']);

    // Laporan
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Profile (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin only - Manajemen Pengguna
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);
    });
});

require __DIR__.'/auth.php';
