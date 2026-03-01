<?php

// database/migrations/2025_05_28_000000_create_branches_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('name');
            $table->string('address');
            $table->string('phone');
            $table->foreignId('company_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('branches');
    }
}
