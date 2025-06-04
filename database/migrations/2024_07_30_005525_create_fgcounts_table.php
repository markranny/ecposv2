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
        Schema::create('fgcounts', function (Blueprint $table) {
            $table->string('JOURNALID', 20);
            $table->string('STORENAME', 20)->nullable();
            $table->dateTime('TRANSDATE')->nullable();
            $table->string('ITEMID', 20)->nullable();
            $table->decimal('COUNTED', 28, 12)->nullable();
            $table->string('VARIANTID', 20)->nullable();
            $table->dateTime('POSTEDDATETIME')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fgcounts');
    }
};
