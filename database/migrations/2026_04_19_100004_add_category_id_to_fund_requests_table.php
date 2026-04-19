<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('fund_requests', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('family_id')
                    ->constrained('categories')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('fund_requests', 'category')) {
            Schema::table('fund_requests', function (Blueprint $table) {
                $table->string('category')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            if (Schema::hasColumn('fund_requests', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });
    }
};
