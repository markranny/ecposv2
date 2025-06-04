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
        Schema::create('importproducts', function (Blueprint $table) {
            $table->string('itemid', 20)->primary();
            $table->string('description')->nullable();
            $table->string('searchalias')->nullable();
            $table->string('notes')->nullable();
            $table->string('retailgroup')->nullable();
            $table->string('retaildepartment')->nullable();
            $table->string('salestaxgroup')->nullable();
            $table->string('costprice')->nullable();
            $table->string('salesprice')->nullable();
            $table->string('barcodesetup')->nullable();
            $table->string('barcode')->nullable();
            $table->string('barcodeunit')->nullable();
            $table->string('activestatus')->nullable();
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importproducts');
    }
};
