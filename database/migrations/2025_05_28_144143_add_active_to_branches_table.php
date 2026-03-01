<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveToBranchesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('branches', 'active')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->boolean('active')
                      ->default(true)
                      ->after('company_id');
            });
        }
        if (!Schema::hasColumn('branches', 'main')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->boolean('main')
                      ->default(false)
                      ->after('company_id');
            });
        }
    }

    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['active', 'main']);
        });
    }
}
