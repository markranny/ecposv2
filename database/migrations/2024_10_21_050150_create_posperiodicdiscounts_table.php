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
        Schema::create('posperiodicdiscounts', function (Blueprint $table) {
            $table->string('offerid', 20)->primary(); 
            $table->string('description', 60);
            $table->tinyInteger('status')->nullable();
            $table->string('discvalidperiodid', 20)->nullable();
            $table->integer('discounttype')->nullable();
            $table->decimal('dealpricevalue', 28, 12)->nullable();
            $table->decimal('discountpctvalue', 28, 12)->nullable();
            $table->decimal('discountamountvalue', 28, 12)->nullable();
            $table->string('pricegroup', 20)->nullable();
            $table->integer('triggered')->nullable();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posperiodicdiscounts');
    }
};
