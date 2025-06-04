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
        Schema::create('carttables', function (Blueprint $table) {
            $table->id();
            $table->string('cartid', 50)->unique();
            $table->string('store', 255);
            $table->string('staff', 20)->nullable();
            $table->string('custaccount', 20)->nullable();
            $table->decimal('netamount', 28, 2)->nullable();
            $table->decimal('costamount', 28, 2)->nullable();
            $table->decimal('grossamount', 28, 2)->nullable();
            $table->decimal('partialpayment', 28, 2)->nullable();
            $table->tinyInteger('transactionstatus')->nullable();
            $table->decimal('discamount', 28, 2)->nullable();
            $table->decimal('custdiscamount', 28, 2)->nullable();
            $table->decimal('totaldiscamount', 28, 2)->nullable();
            $table->integer('numberofitems')->nullable();
            $table->string('refundreceiptid', 20)->nullable();
            $table->dateTime('createddate')->nullable();
            $table->decimal('priceoverride', 28, 2)->nullable();
            $table->string('comment', 60)->nullable();
            $table->string('receiptemail', 80)->nullable();
            $table->decimal('markupamount', 28, 2)->nullable();
            $table->string('markupdescription', 150)->nullable();
            $table->decimal('taxinclinprice', 28, 2)->nullable();
            $table->decimal('vat', 28, 2)->nullable();
            $table->integer('window_number')->nullable();
            $table->timestamps();
            
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carttables');
    }
};
