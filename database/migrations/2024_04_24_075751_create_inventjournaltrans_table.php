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
        Schema::create('inventjournaltrans', function (Blueprint $table) {
            $table->string('JOURNALID', 20);
            $table->string('LINENUM', 20)->nullable();
            $table->dateTime('TRANSDATE')->nullable();
            $table->string('ITEMID', 20)->nullable();
            $table->decimal('ADJUSTMENT', 28, 12)->nullable();
            $table->decimal('COSTPRICE', 28, 12)->nullable();
            $table->decimal('PRICEUNIT', 28, 12)->nullable();
            $table->decimal('SALESAMOUNT', 28, 12)->nullable();
            $table->decimal('INVENTONHAND', 28, 12)->nullable();
            $table->decimal('COUNTED', 28, 12)->nullable();
            $table->string('REASONREFRECID', 20)->nullable();
            $table->string('VARIANTID', 20)->nullable();
            $table->integer('POSTED')->nullable();
            $table->dateTime('POSTEDDATETIME')->nullable();
            $table->string('UNITID', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventjournaltrans');
    }
};
