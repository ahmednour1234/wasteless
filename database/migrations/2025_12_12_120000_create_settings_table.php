<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول settings كان مستخدَم في الكود (App\Models\Setting) لكن من غير
 * مايجريشن تنشئه، فأي setup جديد كان بيقع عند مايجريشن
 * add_commission_percentage_to_settings_table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('img')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->string('lang')->nullable();   // خط الطول
                $table->string('lat')->nullable();    // خط العرض
                $table->string('email')->nullable();
                $table->text('social_media')->nullable();
                $table->unsignedBigInteger('view_web')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
