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
        Schema::create('nubersequencetables', function (Blueprint $table) {
            $table->string('NUMBERSEQUENCE', 20)->primary();
            $table->string('TXT', 30)->nullable();
            $table->integer('LOWEST')->nullable()->default(0);
            $table->integer('HIGHEST')->nullable()->default(0);
            $table->tinyInteger('BLOCKED')->nullable()->default(0);
            $table->string('STOREID', 20);
            $table->tinyInteger('CANBEDELETED')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nubersequencetables');
    }
};
