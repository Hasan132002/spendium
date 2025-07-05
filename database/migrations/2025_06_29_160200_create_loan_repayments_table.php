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
       Schema::create('loan_repayments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('loan_id');
    $table->decimal('amount', 12, 2);
    $table->date('date');
    $table->string('note')->nullable();
    $table->timestamps();

    $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};
