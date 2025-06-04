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
        Schema::create('posdiscvalidationperiods', function (Blueprint $table) {
            $table->string('ID', 20);
            $table->string('DESCRIPTION', 60);
            $table->dateTime('STARTINGDATE')->nullable();
            $table->dateTime('ENDINGDATE')->nullable();
            $table->primary('ID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posdiscvalidationperiods');
    }
};
