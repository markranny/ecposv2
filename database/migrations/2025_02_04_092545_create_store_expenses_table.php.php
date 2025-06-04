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
        Schema::create('store_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('expense_type');
            $table->decimal('amount', 10, 2);
            $table->string('received_by');
            $table->string('approved_by');
            $table->date('effect_date');
            $table->string('store_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_expenses');
    }
};