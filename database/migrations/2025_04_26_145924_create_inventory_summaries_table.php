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
        Schema::create('inventory_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('itemid');
            $table->string('itemname');
            $table->string('storename');
            $table->decimal('beginning', 10, 2)->default(0);
            $table->decimal('received_delivery', 10, 2)->default(0);
            $table->decimal('stock_transfer', 10, 2)->default(0);
            $table->decimal('sales', 10, 2)->default(0);
            $table->decimal('bundle_sales', 10, 2)->default(0);
            $table->decimal('throw_away', 10, 2)->default(0);
            $table->decimal('early_molds', 10, 2)->default(0);
            $table->decimal('pull_out', 10, 2)->default(0);
            $table->decimal('rat_bites', 10, 2)->default(0);
            $table->decimal('ant_bites', 10, 2)->default(0);
            $table->decimal('item_count', 10, 2)->default(0);
            $table->decimal('ending', 10, 2)->default(0);
            $table->decimal('variance', 10, 2)->default(0);
            $table->date('report_date');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            // Composite unique index
            $table->unique(['itemid', 'storename', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_summaries');
    }
};