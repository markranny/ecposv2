<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('rboinventtables', function (Blueprint $table) {
            $table->string('itemid', 20)->primary(); // Declaring 'itemid' as the primary key
            $table->integer('itemtype')->default(0);
            $table->string('itemgroup', 250)->default('');
            $table->string('itemdepartment', 250)->default('');
            $table->tinyInteger('zeropricevalid')->default(0);
            $table->dateTime('dateblocked')->default('1970-01-01 00:00:00'); // Changed default datetime value
            $table->dateTime('datetobeblocked')->default('1970-01-01 00:00:00'); // Changed default datetime value
            $table->tinyInteger('blockedonpos')->default(0);
            $table->tinyInteger('Activeondelivery')->default(0);
            $table->string('barcode', 20)->default('');
            $table->dateTime('datetoactivateitem')->nullable();
            $table->tinyInteger('mustselectuom')->default(0);
            $table->timestamps(); // Add timestamps for created_at and updated_at
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });

        // Modify column defaults if necessary
        Schema::table('rboinventtables', function (Blueprint $table) {
            $table->integer('itemtype')->default(0)->change();
            $table->tinyInteger('zeropricevalid')->default(0)->change();
            $table->tinyInteger('blockedonpos')->default(0)->change();
            $table->dateTime('dateblocked')->default('1970-01-01 00:00:00')->change(); // Changed default datetime value
            $table->dateTime('datetobeblocked')->default('1970-01-01 00:00:00')->change(); // Changed default datetime value
            $table->tinyInteger('mustselectuom')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rboinventtables');
    }
};
