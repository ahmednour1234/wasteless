<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'promo_code_id')) {
                $table->unsignedBigInteger('promo_code_id')->nullable()->after('has_bonus_discount');
                $table->string('promo_code')->nullable()->after('promo_code_id');
                $table->string('promo_type')->nullable()->after('promo_code');
                $table->decimal('promo_value', 10, 2)->nullable()->after('promo_type');
                $table->decimal('promo_discount', 10, 2)->default(0)->after('promo_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['promo_code_id', 'promo_code', 'promo_type', 'promo_value', 'promo_discount']);
        });
    }
};
