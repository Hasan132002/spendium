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
      Schema::create('goals', function (Blueprint $table) {
    $table->id();
    $table->foreignId('family_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
    $table->string('title');
    $table->decimal('target_amount', 12, 2);
    $table->decimal('saved_amount', 12, 2)->default(0);
    $table->enum('type', ['family', 'personal']);
    $table->string('status')->default('active');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
