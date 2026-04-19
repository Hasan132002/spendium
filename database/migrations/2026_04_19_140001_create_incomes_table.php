<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('source', ['salary', 'business', 'freelance', 'rental', 'investment', 'gift', 'other'])->default('other');
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->text('note')->nullable();
            $table->date('received_on');
            $table->boolean('recurring')->default(false);
            $table->enum('recurrence_interval', ['monthly', 'weekly', 'yearly'])->nullable();
            $table->timestamps();

            $table->index(['user_id', 'received_on']);
            $table->index(['family_id', 'received_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
