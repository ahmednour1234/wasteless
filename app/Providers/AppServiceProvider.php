<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
 public function boot()
    {
        // 1) اضبط الـ PHP timezone
        date_default_timezone_set(config('app.timezone'));

        // 2) اجبر Carbon على استعمال توقيت القاهرة عند التسلسل
        Carbon::serializeUsing(function (Carbon $date) {
            // toIso8601String يُعيد مثل "2025-06-15T23:09:44+02:00"
            // بدل toJSON التي ترجع UTC مع "Z"
            return $date->copy()
                        ->tz(config('app.timezone'))
                        ->toIso8601String();
        });
    }
}
