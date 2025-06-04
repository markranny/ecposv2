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
        Schema::create('barcodes', function (Blueprint $table) {
            $table->string('barcode', 13)->default('');
            $table->string('DESCRIPTION', 50)->nullable();
            $table->tinyInteger('ISUSE')->default(1); 
            $table->string('GENERATEBY', 50)->nullable();
            $table->dateTime('GENERATEDATE')->nullable();
            $table->string('MODIFIEDBY', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcodes');
    }
};
