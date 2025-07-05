<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // auto-increment primary key
            $table->string('CardCode');
            $table->string('CardName');
            $table->integer('DocNum')->unique();
            $table->date('DocDate');
            $table->decimal('DocTotal', 15, 2);
            $table->text('Comments')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
