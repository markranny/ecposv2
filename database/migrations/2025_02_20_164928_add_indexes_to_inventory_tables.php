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
        Schema::table('stockcountingtrans', function (Blueprint $table) {
            $table->index(['itemid', 'storename', 'transdate'], 'idx_stockcounting_item_store_date');
            $table->index(['storename'], 'idx_stockcounting_storename');
            $table->index(['transdate'], 'idx_stockcounting_transdate');
            $table->index(['WASTETYPE'], 'idx_stockcounting_wastetype');
        });

        Schema::table('rbotransactionsalestrans', function (Blueprint $table) {
            $table->index(['itemid', 'store', 'createddate'], 'idx_sales_item_store_date');
            $table->index(['transactionid', 'linenum'], 'idx_sales_transaction_line');
        });

        Schema::table('inventtables', function (Blueprint $table) {
            $table->index(['itemid'], 'idx_invent_itemid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('stockcountingtrans', function (Blueprint $table) {
            $table->dropIndex('idx_stockcounting_item_store_date');
            $table->dropIndex('idx_stockcounting_storename');
            $table->dropIndex('idx_stockcounting_transdate');
            $table->dropIndex('idx_stockcounting_wastetype');
        });

        Schema::table('rbotransactionsalestrans', function (Blueprint $table) {
            $table->dropIndex('idx_sales_item_store_date');
            $table->dropIndex('idx_sales_transaction_line');
        });

        Schema::table('inventtables', function (Blueprint $table) {
            $table->dropIndex('idx_invent_itemid');
        });
    }
};
