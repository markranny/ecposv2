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
        Schema::create('wastedeclarations', function (Blueprint $table) {
            $table->id('JOURNALID'); 
            $table->string('DESCRIPTION');
            $table->integer('POSTED');
            $table->dateTime('POSTEDDATETIME');
            $table->dateTime('CREATEDDATETIME');
            $table->string('STOREID', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wastedeclarations');
    }
};
