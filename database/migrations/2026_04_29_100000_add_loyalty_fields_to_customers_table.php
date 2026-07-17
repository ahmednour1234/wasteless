<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'loyalty_points')) {
                $table->unsignedInteger('loyalty_points')->default(0)->after('img');
            }
            if (! Schema::hasColumn('customers', 'loyalty_bonus_expires_at')) {
                $table->timestamp('loyalty_bonus_expires_at')->nullable()->after('loyalty_points');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points', 'loyalty_bonus_expires_at']);
        });
    }
};
