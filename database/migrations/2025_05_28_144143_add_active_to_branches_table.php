<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveToBranchesTable extends Migration
{
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->boolean('active')
                  ->default(true)
                  ->after('company_id');
        });
    }

    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
}
