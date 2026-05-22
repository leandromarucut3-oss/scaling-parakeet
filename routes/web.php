<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\WithdrawalManagementController;
use App\Http\Controllers\Admin\DepositManagementController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\FranchiseController;
use App\Http\Controllers\BackupController;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
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
})->middleware(['auth'])->name('dashboard');

Route::get('/buy-shares', function () {
    return Inertia::render('BuyShares');
})->middleware(['auth'])->name('shares.buy');



/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [UserManagementController::class, 'index'])
            ->name('dashboard');

        Route::get('/withdrawals', [WithdrawalManagementController::class, 'index'])
            ->name('withdrawals');

        Route::get('/deposits', [DepositManagementController::class, 'index'])
            ->name('deposits');

        Route::get('/send-funds', [UserManagementController::class, 'sendFunds'])
            ->name('send-funds');

        Route::get('/send-package', [UserManagementController::class, 'sendPackage'])
            ->name('send-package');

        Route::get('/package-slots', [UserManagementController::class, 'packageSlots'])
            ->name('package-slots');

        Route::post('/package-slots', [UserManagementController::class, 'updatePackageSlots'])
            ->name('package-slots.update');

        Route::get('/users/{user}', [UserManagementController::class, 'show'])
            ->name('users.show');

        Route::delete('/users/{user}/deposits/{purchase}', [UserManagementController::class, 'destroyDeposit'])
            ->name('users.deposit.destroy');

        Route::get('/recent-transactions', [UserManagementController::class, 'recentTransactions'])
            ->name('recent-transactions');

        Route::post('/deposits/{purchase}/approve', [DepositManagementController::class, 'approve'])
            ->name('deposits.approve');
        Route::post('/deposits/{purchase}/reject', [DepositManagementController::class, 'reject'])
            ->name('deposits.reject');

        Route::post('/users/{user}/transfer', [UserManagementController::class, 'transfer'])
            ->name('users.transfer');

        Route::post('/users/{user}/grant-package', [UserManagementController::class, 'grantPackage'])
            ->name('users.grant-package');

        Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalManagementController::class, 'approve'])
            ->name('withdrawals.approve');

        Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalManagementController::class, 'reject'])
            ->name('withdrawals.reject');

        /*
        |--------------------------------------------------------------------------
        | BACKUP ROUTE
        |--------------------------------------------------------------------------
        */

        Route::get('/backup', [BackupController::class, 'download'])
            ->name('backup.download');

        /*
        |--------------------------------------------------------------------------
        | AUTOMATED LIVE DEPLOYMENT ROUTE
        |--------------------------------------------------------------------------
        */

        Route::get('/deploy-live-server', [BackupController::class, 'runDeployment'])
            ->name('system.deploy');
    });



/*
|--------------------------------------------------------------------------
| PROFILE ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});



/*
|--------------------------------------------------------------------------
| USER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/invites', [InviteController::class, 'index'])
        ->name('invites');

    Route::get('/statements', [StatementController::class, 'index'])
        ->name('statements');

    Route::post('/buy-shares', [PurchaseController::class, 'store'])
        ->name('shares.purchase');

    Route::post('/withdrawals', [WithdrawalController::class, 'store'])
        ->name('withdrawals.store');

    Route::get('/transfer', [TransferController::class, 'index'])
        ->name('transfer.index');

    Route::post('/transfer', [TransferController::class, 'store'])
        ->name('transfer.store');

    Route::get('/contract', [ContractController::class, 'index'])
        ->name('contract.index');

    Route::post('/contract', [ContractController::class, 'store'])
        ->name('contract.store');

    Route::post('/franchise', [FranchiseController::class, 'store'])
        ->name('franchise.apply');
});



require __DIR__.'/auth.php';
