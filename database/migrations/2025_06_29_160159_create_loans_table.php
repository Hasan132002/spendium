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
        Schema::create('loans', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('family_id');
    $table->unsignedBigInteger('loan_category_id');
    $table->string('lender')->nullable();
    $table->decimal('amount', 12, 2);
    $table->text('purpose')->nullable();
    $table->decimal('remaining_amount', 12, 2);
    $table->enum('status', ['pending', 'partially_paid', 'paid'])->default('pending');
    $table->date('due_date')->nullable();
    $table->timestamps();

    $table->foreign('family_id')->references('id')->on('families')->onDelete('cascade');
    $table->foreign('loan_category_id')->references('id')->on('loan_categories')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
