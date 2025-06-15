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
            $table->id();
            $table->string('itemid')->nullable();
            $table->string('description')->nullable();
            $table->decimal('costprice', 10, 2)->default(0);
            $table->decimal('salesprice', 10, 2)->default(0);
            $table->string('searchalias')->nullable();
            $table->text('notes')->nullable();
            $table->string('retailgroup')->nullable();
            $table->string('retaildepartment')->default('NON PRODUCT');
            $table->string('barcode')->nullable();
            $table->tinyInteger('activestatus')->default(1);
            $table->string('barcodesetup')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('itemid');
            $table->index('barcode');
            $table->index('retailgroup');
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