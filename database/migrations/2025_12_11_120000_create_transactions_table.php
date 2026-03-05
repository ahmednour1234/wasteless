<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('external_id')->unique();
            $table->enum('payment_type', ['whish_money', 'omt_pay', 'bank'])->default('whish_money');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');
            $table->text('collect_url')->nullable();
            $table->string('collect_status')->nullable();
            $table->string('payer_phone_number')->nullable();
            $table->text('invoice')->nullable();
            $table->string('success_callback_url');
            $table->string('failure_callback_url');
            $table->string('success_redirect_url');
            $table->string('failure_redirect_url');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
