<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('projects', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('name_ar');
      $table->string('title');
      $table->string('title_ar');
      $table->string('qrcode')->nullable(); // Add this line in the migration file

      $table->timestamps(); // created_at and updated_at columns
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('projects');
  }
}
