<?php

use App\Http\Controllers\dashboard\ReviewController;
use App\Http\Controllers\dashboard\BranchController;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\dashboard\RolesController;
use App\Http\Controllers\dashboard\SettingController;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\dashboard\BundleController;
use App\Http\Controllers\dashboard\CategoryController;
use App\Http\Controllers\dashboard\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\dashboard\CompanyController;
use App\Http\Controllers\dashboard\CustomerController;
use App\Http\Controllers\dashboard\TransactionController;

Route::middleware('auth')
  ->get('/', [Analytics::class, 'index'])
  ->name('dashboard-analytics');

// Login Route
Route::get('/login', [LoginBasic::class, 'index'])->name('login-basic');
Route::post('login', [LoginBasic::class, 'login'])->name('login');
Route::post('logout', [LoginBasic::class, 'logout'])->name('logout');

// Locale
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// Main Page Route (Protected)

// Users Routes (Protected)
Route::middleware('auth')
  ->prefix('users')
  ->name('users.')
  ->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::put('/{id}', [UserController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    Route::get('/export', [UserController::class, 'export'])->name('export');
  });
Route::middleware('auth')->group(function () {
     Route::get('/orders', [\App\Http\Controllers\dashboard\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{orderId}', [\App\Http\Controllers\dashboard\OrderController::class, 'show'])->name('orders.show');
  Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
  Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
  Route::post('/transactions/{id}/process', [TransactionController::class, 'processTransaction'])->name('transactions.process');
  Route::resource('companies', CompanyController::class)->only(['index', 'show']);
  Route::patch('companies/{company}/toggle', [CompanyController::class, 'toggleStatus'])->name('companies.toggle');
Route::put('companies/{company}/update-password', [CompanyController::class, 'updatePassword'])->name('companies.updatePassword');

  Route::get('/branches', [BranchController::class, 'index'])->name('branches');
  Route::prefix('category')
    ->name('category.')
    ->group(function () {
      Route::get('/', [CategoryController::class, 'index'])->name('index'); // GET  /category
      Route::get('/create', [CategoryController::class, 'create'])->name('create'); // GET  /category/create
      Route::post('/', [CategoryController::class, 'store'])->name('store'); // POST /category
      Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit'); // GET  /category/{category}/edit
      Route::match(['put', 'patch'], '/{category}', [CategoryController::class, 'update'])->name('update'); // PUT|PATCH /category/{category}
      Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy'); // DELETE /category/{category}
      Route::patch('/{category}/toggle', [CategoryController::class, 'toggle'])->name('toggle'); // PATCH /category/{category}/toggle
    });
  Route::prefix('bundels')
    ->name('bundels.')
    ->group(function () {
      Route::get('/', [BundleController::class, 'index'])->name('index');
            Route::get('/create', [BundleController::class, 'create'])->name('create');

      Route::get('/show/{bundle}', [BundleController::class, 'show'])->name('show');
      Route::post('/{id}/toggle', [BundleController::class, 'toggle'])->name('toggle');
       Route::post('/{id}/toggle', [BundleController::class, 'toggle'])->name('toggle');
        Route::post('/store', [BundleController::class, 'store'])->name('store');
    });
  Route::prefix('customers')
    ->name('customers.')
    ->group(function () {
      Route::get('/', [CustomerController::class, 'index'])->name('index');

      Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
    });
  Route::prefix('reviews')
    ->name('reviews.')
    ->group(function () {
      Route::get('/', [ReviewController::class, 'index'])->name('index');

      Route::get('/{review}', [ReviewController::class, 'show'])->name('show');

      Route::patch('/{review}/toggle', [ReviewController::class, 'toggle'])->name('toggle');
    });

  Route::get('/roles', [RolesController::class, 'index'])->name('dashboard-users-roles');
  Route::post('/roles/store', [RolesController::class, 'store'])->name('roles.store');
  Route::put('/roles/{id}', [RolesController::class, 'update'])->name('roles.update');
  Route::delete('/roles/{id}', [RolesController::class, 'destroy'])->name('roles.destroy');
});

// Setting (Protected)
Route::middleware('auth')
  ->prefix('setting')
  ->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('dashboard-setting');
    Route::post('/', [SettingController::class, 'store'])->name('dashboard-setting-store');
  });
