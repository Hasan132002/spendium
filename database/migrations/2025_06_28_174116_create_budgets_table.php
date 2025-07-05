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
    Schema::create('budgets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('family_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->nullable()->constrained('users'); // null for total budget
    $table->string('category');
    $table->decimal('amount', 10, 2);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
