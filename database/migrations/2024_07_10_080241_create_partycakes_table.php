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
        Schema::create('partycakes', function (Blueprint $table) {
            $table->id();
            $table->string('COSNO', 60)->nullable();
            $table->string('BRANCH', 250)->nullable();
            $table->dateTime('DATEORDER')->nullable();
            $table->string('CUSTOMERNAME', 250)->nullable();
            $table->string('ADDRESS', 250)->nullable();
            $table->string('TELNO', 250)->nullable();
            $table->date('DATEPICKEDUP')->nullable();
            $table->time('TIMEPICKEDUP')->nullable(); 
            $table->date('DELIVERED')->nullable();
            $table->time('TIMEDELIVERED')->nullable(); 
            $table->string('DEDICATION', 250)->nullable();
            $table->string('BDAYCODENO', 250)->nullable();
            $table->string('FLAVOR', 250)->nullable();
            $table->string('MOTIF', 250)->nullable();
            $table->string('ICING', 250)->nullable();
            $table->string('OTHERS', 250)->nullable();
            $table->decimal('SRP', 28, 12)->nullable();
            $table->decimal('DISCOUNT', 28, 12)->nullable();
            $table->decimal('PARTIALPAYMENT', 28, 12)->nullable();
            $table->decimal('NETAMOUNT', 28, 12)->nullable();
            $table->decimal('BALANCEAMOUNT', 28, 12)->nullable();
            $table->string('STATUS', 250)->nullable();
            $table->string('file_path', 250)->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partycakes');
    }
};
