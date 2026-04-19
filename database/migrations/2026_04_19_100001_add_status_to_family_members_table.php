<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            if (!Schema::hasColumn('family_members', 'status')) {
                $table->enum('status', ['pending', 'accepted', 'rejected'])
                    ->default('accepted')
                    ->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            if (Schema::hasColumn('family_members', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
