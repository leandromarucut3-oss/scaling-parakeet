<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\WithdrawalManagementController;
use App\Http\Controllers\Admin\DepositManagementController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/buy-shares', function () {
    return Inertia::render('BuyShares');
})->middleware(['auth', 'verified'])->name('shares.buy');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [UserManagementController::class, 'index'])->name('dashboard');
    Route::get('/withdrawals', [WithdrawalManagementController::class, 'index'])->name('withdrawals');
    Route::get('/deposits', [DepositManagementController::class, 'index'])->name('deposits');
    Route::post('/users/{user}/transfer', [UserManagementController::class, 'transfer'])->name('users.transfer');
    Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalManagementController::class, 'approve'])
        ->name('withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalManagementController::class, 'reject'])
        ->name('withdrawals.reject');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/buy-shares', [PurchaseController::class, 'store'])->name('shares.purchase');
    Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
});

require __DIR__.'/auth.php';
