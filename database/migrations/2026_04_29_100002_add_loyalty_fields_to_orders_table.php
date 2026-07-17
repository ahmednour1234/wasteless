<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'loyalty_discount')) {
                $table->decimal('loyalty_discount', 10, 2)->default(0)->after('total_discount');
            }
            if (! Schema::hasColumn('orders', 'points_redeemed')) {
                $table->unsignedInteger('points_redeemed')->default(0)->after('loyalty_discount');
            }
            if (! Schema::hasColumn('orders', 'has_bonus_discount')) {
                $table->boolean('has_bonus_discount')->default(false)->after('points_redeemed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['loyalty_discount', 'points_redeemed', 'has_bonus_discount']);
        });
    }
};
