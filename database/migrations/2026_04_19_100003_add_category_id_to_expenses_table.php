<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('budget_id')
                    ->constrained('categories')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });
    }
};
