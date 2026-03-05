<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('commission_percentage', 5, 2)->nullable()->after('currency');
            $table->decimal('commission_amount', 15, 2)->default(0)->after('commission_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['commission_percentage', 'commission_amount']);
        });
    }
};
