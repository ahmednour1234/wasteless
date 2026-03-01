<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('pdfs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('project_id')->constrained()->onDelete('cascade'); // Foreign key to projects table
      $table->string('name');
      $table->string('name_ar');
      $table->string('qrcode');
      $table->string('pdf');
      $table->integer('size'); // Size of the PDF file
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('pdfs');
  }
};
