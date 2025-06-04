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
        Schema::create('inventtablemodules', function (Blueprint $table) {
            $table->string('itemid', 20);
            $table->integer('moduletype');
            $table->string('unitid', 20)->default('PCS');
            $table->decimal('price', 28, 12)->nullable();
            $table->tinyInteger('priceunit')->default(1);
            $table->decimal('priceincltax', 28, 12)->nullable();
            $table->decimal('quantity', 28, 12)->default(0);
            $table->decimal('lowestqty', 28, 12)->nullable();
            $table->decimal('highestqty', 28, 12)->nullable();
            $table->tinyInteger('blocked')->nullable();
            $table->string('inventlocationid', 20)->nullable();
            $table->dateTime('pricedate')->nullable();
            $table->string('taxitemgroupid', 20)->nullable();
            $table->primary(['itemid', 'moduletype']);
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventtablemodules');
    }
};
