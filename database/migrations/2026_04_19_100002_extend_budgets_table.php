<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            if (!Schema::hasColumn('budgets', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('user_id')
                    ->constrained('categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('budgets', 'initial_amount')) {
                $table->decimal('initial_amount', 12, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('budgets', 'type')) {
                $table->enum('type', ['family', 'assigned'])->default('assigned')->after('initial_amount');
            }
            if (!Schema::hasColumn('budgets', 'month')) {
                $table->string('month', 7)->nullable()->after('type');
            }
        });

        if (Schema::hasColumn('budgets', 'category')) {
            Schema::table('budgets', function (Blueprint $table) {
                $table->string('category')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            foreach (['month', 'type', 'initial_amount'] as $col) {
                if (Schema::hasColumn('budgets', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('budgets', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });
    }
};
