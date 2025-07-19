<?php

// database/migrations/xxxx_xx_xx_create_reactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
       Schema::create('reactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->unsignedBigInteger('reactable_id');
    $table->string('reactable_type'); // "App\Models\Post" or "App\Models\Comment"
    $table->string('type')->default('like'); // like, love, etc.
    $table->timestamps();

    $table->unique(['user_id', 'reactable_id', 'reactable_type']); // 1 user = 1 reaction per item
});

    }

    public function down(): void {
        Schema::dropIfExists('reactions');
    }
};
