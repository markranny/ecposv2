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
        Schema::create('inventitembarcodes', function (Blueprint $table) {
            $table->string('ITEMBARCODE', 80)->primary();
            $table->string('ITEMID', 20)->nullable();
            $table->string('BARCODESETUPID', 20)->default('00000001'); 
            $table->string('DESCRIPTION', 60)->nullable();
            $table->decimal('QTY', 28, 12)->default(0); 
            $table->string('UNITID', 20)->default(1); 
            $table->string('RBOVARIANTID', 20)->nullable();
            $table->tinyInteger('BLOCKED')->nullable();
            $table->string('MODIFIEDBY', 100)->nullable();

            // Set table engine to InnoDB
            $table->engine = 'InnoDB';
            $table->timestamps();
        });
    // Add index for ITEMBARCODE
    Schema::table('inventitembarcodes', function (Blueprint $table) {
        $table->index('ITEMBARCODE', 'I_1213BARCODEIDX');
    });
    
    // Set default values using DB::statement
    DB::statement('ALTER TABLE inventitembarcodes MODIFY ITEMBARCODE VARCHAR(80) DEFAULT ""');
    DB::statement('ALTER TABLE inventitembarcodes MODIFY ITEMID VARCHAR(20) DEFAULT ""');
    DB::statement('ALTER TABLE inventitembarcodes MODIFY BARCODESETUPID VARCHAR(20) DEFAULT ""');
    DB::statement('ALTER TABLE inventitembarcodes MODIFY DESCRIPTION VARCHAR(60) DEFAULT ""');
    DB::statement('ALTER TABLE inventitembarcodes MODIFY QTY DECIMAL(28, 12) DEFAULT 0');
    DB::statement('ALTER TABLE inventitembarcodes MODIFY UNITID VARCHAR(20) DEFAULT ""');
    DB::statement('ALTER TABLE inventitembarcodes MODIFY RBOVARIANTID VARCHAR(20) DEFAULT ""');
    DB::statement('ALTER TABLE inventitembarcodes MODIFY BLOCKED TINYINT DEFAULT 0');
    DB::statement('ALTER TABLE inventitembarcodes MODIFY MODIFIEDBY VARCHAR(100) DEFAULT "?"');
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventitembarcodes');
    }
};
