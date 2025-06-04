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
        Schema::create('rbotransactiontables', function (Blueprint $table) {
            $table->string('transactionid', 100); 
            $table->integer('type')->nullable();
            $table->string('receiptid', 20)->nullable(); 
            $table->string('store', 20)->nullable();
            $table->string('staff', 20)->nullable();
            $table->string('custaccount', 20)->nullable();
            $table->decimal('netamount', 28, 2)->nullable();
            $table->decimal('costamount', 28, 2)->nullable(); 
            $table->decimal('grossamount', 28, 2)->nullable(); 
            $table->decimal('partialpayment', 28, 2)->nullable();
            $table->tinyInteger('transactionstatus')->nullable();
            $table->decimal('discamount', 28, 2)->nullable();
            $table->decimal('cashamount', 28, 2)->nullable();
            $table->decimal('custdiscamount', 28, 2)->nullable();
            $table->decimal('totaldiscamount', 28, 2)->nullable();
            $table->decimal('numberofitems', 28, 2)->nullable(); 
            $table->string('refundreceiptid', 20)->nullable();
            $table->dateTime('refunddate')->nullable(); 
            $table->string('returnedby', 255)->nullable(); 
            $table->string('currency', 3)->nullable();
            $table->string('zreportid', 20)->nullable();
            $table->dateTime('createddate')->nullable();
            $table->decimal('priceoverride', 28, 2)->nullable();
            $table->string('comment', 60)->nullable();
            $table->string('receiptemail', 80)->nullable();
            $table->decimal('markupamount', 28, 2)->nullable();
            $table->string('markupdescription', 150)->nullable();
            $table->decimal('taxinclinprice', 28, 2)->nullable();
            $table->decimal('netamountnotincltax', 28, 2)->nullable();
            $table->integer('window_number')->nullable();
            $table->decimal('charge', 28, 2)->nullable();
            $table->decimal('gcash', 28, 2)->nullable();
            $table->decimal('paymaya', 28, 2)->nullable();
            $table->decimal('cash', 28, 2)->nullable();
            $table->decimal('card', 28, 2)->nullable();
            $table->decimal('loyaltycard', 28, 2)->nullable();
            $table->decimal('foodpanda', 28, 2)->nullable();
            $table->decimal('grabfood', 28, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rbotransactiontables');
    }
};
