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
        Schema::create('posvalidationperiods', function (Blueprint $table) {
            $table->id();
            $table->string('description', 60);
            $table->dateTime('startingdate')->nullable();
            $table->dateTime('endingdate')->nullable();
            $table->integer('startingtime')->nullable();
            $table->integer('endingtime')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posvalidationperiods');
    }
};
