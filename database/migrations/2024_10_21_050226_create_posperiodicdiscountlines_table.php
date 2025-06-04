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
        Schema::create('posperiodicdiscountlines', function (Blueprint $table) {
            $table->id();
            $table->string('offerid', 20);
            $table->string('itemid', 20);
            $table->integer('qty')->nullable();
            $table->decimal('dealpriceordiscpct', 28, 12)->nullable();
            $table->string('linegroup', 60);
            $table->integer('disctype')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posperiodicdiscountlines');
    }
};
