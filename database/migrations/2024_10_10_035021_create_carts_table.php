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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('cartid', 50);
            $table->string('itemid', 20)->nullable(); 
            $table->string('itemname', 255)->nullable(); 
            $table->string('itemgroup', 20)->nullable(); 
            $table->decimal('price', 28, 2)->nullable(); 
            $table->decimal('netprice', 28, 2)->nullable(); 
            $table->decimal('qty', 28, 2)->nullable(); 
            $table->decimal('discamount', 28, 2)->nullable(); 
            $table->decimal('costamount', 28, 2)->nullable(); 
            $table->decimal('netamount', 28, 2)->nullable(); 
            $table->decimal('grossamount', 28, 2)->nullable(); 
            $table->string('custaccount', 20)->nullable(); 
            $table->string('store', 20); 
            $table->decimal('priceoverride', 28, 2)->nullable(); 
            $table->string('paymentmethod', 20)->nullable(); 
            $table->string('partial_payment', 20)->nullable(); 
            $table->string('staff', 20)->nullable(); 
            $table->string('discofferid', 20)->nullable(); 
            $table->decimal('linedscamount', 28, 2)->nullable(); 
            $table->decimal('linediscpct', 28, 2)->nullable(); 
            $table->decimal('custdiscamount', 28, 2)->nullable(); 
            $table->string('unit', 20)->nullable(); 
            $table->decimal('unitqty', 28, 2)->nullable(); 
            $table->decimal('unitprice', 28, 2)->nullable(); 
            $table->decimal('taxamount', 28, 2)->nullable(); 
            $table->dateTime('createddate')->nullable(); 
            $table->string('remarks', 60)->nullable();
            $table->string('inventbatchid', 20)->nullable(); 
            $table->dateTime('inventbatchexpdate')->nullable(); 
            $table->string('giftcard')->nullable(); 
            $table->string('returntransactionid', 20)->nullable(); 
            $table->decimal('returnqty', 28, 2)->nullable(); 
            $table->string('creditmemonumber', 255)->nullable(); 
            $table->decimal('taxinclinprice', 28, 2)->nullable(); 
            $table->string('description', 60)->nullable(); 
            $table->decimal('returnlineid', 28, 2)->nullable(); 
            $table->decimal('priceunit', 28, 2)->nullable(); 
            $table->decimal('netamountnotincltax', 28, 2)->nullable();
            $table->string('storetaxgroup', 20)->nullable(); 
            $table->string('currency', 3)->nullable(); 
            $table->decimal('taxexempt', 28, 2)->nullable(); 
            $table->string('wintransid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
