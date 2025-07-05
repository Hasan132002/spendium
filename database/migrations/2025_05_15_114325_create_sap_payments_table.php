<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('sap_payments', function (Blueprint $table) {
        $table->id();
        $table->string('card_code');
        $table->string('card_name');
        $table->integer('doc_num')->unique();
        $table->date('doc_date');
        $table->decimal('doc_total', 15, 2);
        $table->text('comments')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_payments');
    }
};
