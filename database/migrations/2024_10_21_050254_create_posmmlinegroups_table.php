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
        Schema::create('posmmlinegroups', function (Blueprint $table) {
            $table->string('offerid', 20); 
            $table->string('linegroup', 20);
            $table->integer('noofitemsneeded')->nullable();
            $table->string('description', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posmmlinegroups');
    }
};
