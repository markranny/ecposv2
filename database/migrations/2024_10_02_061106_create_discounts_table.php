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
    Schema::create('discounts', function (Blueprint $table) {
        $table->id(); // This will be the auto-incrementing primary key
        $table->string('DISCOFFERNAME', 250)->nullable();
        $table->integer('PARAMETER')->nullable(); // Removed auto_increment and primary key
        $table->string('DISCOUNTTYPE', 250)->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
