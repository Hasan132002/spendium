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
        Schema::create('loan_contributions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('loan_id');
    $table->unsignedBigInteger('user_id');
    $table->decimal('amount', 12, 2);
    $table->string('note')->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->timestamps();

    $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_contributions');
    }
};
