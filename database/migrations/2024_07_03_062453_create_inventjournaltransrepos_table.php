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
        Schema::create('inventjournaltransrepos', function (Blueprint $table) {
            $table->id();
            $table->string('JOURNALID', 20);
            $table->string('LINENUM', 20)->nullable();
            $table->dateTime('TRANSDATE')->nullable();
            $table->string('ITEMID', 20)->nullable();
            $table->decimal('COUNTED', 28, 0)->nullable();
            $table->string('STORENAME')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventjournaltransrepos');
    }
};
