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
        Schema::create('nubersequencevalues', function (Blueprint $table) {
            $table->string('NUMBERSEQUENCE', 20);
            $table->integer('NEXTREC');
            $table->integer('CARTNEXTREC');
            $table->integer('BUNDLENEXTREC');
            $table->integer('DISCOUNTNEXTREC');
            $table->string('STOREID', 20);
            $table->primary('NUMBERSEQUENCE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nubersequencevalues');
    }
};
