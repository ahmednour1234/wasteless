<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Company\CompanyAuthController;
use App\Http\Controllers\API\User\CompanyController;
use App\Http\Controllers\API\Company\BranchController;
use App\Http\Controllers\API\Company\BundleController;
use App\Http\Controllers\API\Company\CompanyOrderController;
use App\Http\Controllers\API\User\ReviewController;
use App\Http\Controllers\API\User\AuthUserController;
use App\Http\Controllers\API\User\BundleController as PublicBundleController;
use App\Http\Controllers\API\User\CategoryController;
use App\Http\Controllers\API\User\FavouriteController;
use App\Http\Controllers\API\User\OrderController;
use App\Http\Controllers\API\User\PaymentController;

Route::prefix('companies')->group(function () {
  Route::post('signup',     [CompanyAuthController::class, 'signup']);
  Route::post('verify-otp', [CompanyAuthController::class, 'verifyOtp']);
  Route::post('resend-otp', [CompanyAuthController::class, 'resendOtp']);
  Route::post('login',      [CompanyAuthController::class, 'login']);
  Route::middleware('auth:company')
    ->group(function () {
      Route::prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'companyBranches']);
        Route::post('/', [BranchController::class, 'store']);
        Route::put('/{branch}', [BranchController::class, 'update']);
      });
            Route::prefix('orders')->group(function () {

          Route::get('/', [CompanyOrderController::class, 'index']);
    Route::get('/{orderId}', [CompanyOrderController::class, 'show']);
    Route::put('/details/{detailId}/status', [CompanyOrderController::class, 'updateStatus']);
        Route::post('/orderstatus/{id}', [CompanyOrderController::class, 'updateStatusOrder']);
            });

      Route::prefix('bundle')
        ->group(function () {
          Route::get('/',                 [BundleController::class, 'index']);   // جميع الباندلز
                 
          Route::get('/{bundle}',        [BundleController::class, 'show']);    // باندل واحد
          Route::post('/', [BundleController::class, 'store']);
          Route::post('/{bundle}',        [BundleController::class, 'update']);
          Route::post('/{bundle}/toggle', [BundleController::class, 'toggle']);
        });
      Route::post('logout', [CompanyAuthController::class, 'logout']);
    });
});
Route::prefix('user')->group(function () {
  Route::post('register', [AuthUserController::class, 'register']);
  Route::post('login',    [AuthUserController::class, 'login']);
  Route::prefix('branches')->group(function () {
    Route::get('/', [BranchController::class, 'index']);
    Route::get('/{branch}', [BranchController::class, 'show']);
  });
  Route::prefix('company')->group(function () {
    Route::get('/', [CompanyController::class, 'index']);
  });
  Route::prefix('bundles')->group(function () {
    Route::get('/',          [PublicBundleController::class, 'index']);
       Route::get('/indexlasthour',                 [PublicBundleController::class, 'indexlasthour']);   // جميع الباندلز
          Route::get('/indexTomorrowBundles',                 [PublicBundleController::class, 'indexTomorrowBundles']);   // جميع الباندلز
          Route::get('/indexlastchance',                 [PublicBundleController::class, 'indexlastchance']);   // جميع الباندلز

    Route::get('/{bundle}', [PublicBundleController::class, 'show']);
  });
  Route::prefix('bundles/{bundle}')->group(function () {
    Route::get('reviews', [ReviewController::class, 'index']);
    Route::post('reviews', [ReviewController::class, 'store'])->middleware('auth:sanctum');
});
  Route::prefix('payments')->group(function () {
    Route::post('callback/success', [PaymentController::class, 'successCallback']);
    Route::post('callback/failure', [PaymentController::class, 'failureCallback']);
  });

  Route::middleware('auth:sanctum')->group(function () {
            Route::prefix('favourites')->group(function () {
                    Route::get('/{bundleId}', [FavouriteController::class, 'show']);
          Route::get('/', [FavouriteController::class, 'index']);
    Route::post('/', [FavouriteController::class, 'store']);
    Route::delete('/{bundleId}', [FavouriteController::class, 'destroy']);
            });
                        Route::prefix('orders')->group(function () {

                Route::get('/', [OrderController::class, 'index']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::post('/', [OrderController::class, 'store']);
            });

    Route::prefix('payments')->group(function () {
      Route::get('/{transactionId}/status', [PaymentController::class, 'checkStatus']);
    });

    Route::get('profile', [AuthUserController::class, 'me']);      // جلب البيانات
    Route::post('profile', [AuthUserController::class, 'update']);  // تحديث البيانات
    Route::post('logout',  [AuthUserController::class, 'logout']);
      Route::prefix('reviews')->group(function () {
        Route::get('/{review}', [ReviewController::class, 'show']);
});

  });
  Route::get('categories', [CategoryController::class, 'index']);
});
