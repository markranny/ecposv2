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
        Schema::create('rbospecialgroups', function (Blueprint $table) {
            $table->string('GROUPID', 20)->primary();
            $table->string('NAME', 60)->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE rbospecialgroups ENGINE = InnoDB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rbospecialgroups');
    }
};
