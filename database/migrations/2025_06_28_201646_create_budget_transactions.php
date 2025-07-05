<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('budget_id')->constrained();
    $table->foreignId('user_id')->nullable(); // Who triggered
    $table->enum('action', ['add', 'deduct']);
    $table->decimal('amount');
    $table->string('source')->nullable(); // 'expense', 'fund_request', 'top_up'
    $table->unsignedBigInteger('source_id')->nullable(); // link to expense/fund_request
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_transactions');
    }
};
