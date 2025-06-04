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
        Schema::create('rbostoretables', function (Blueprint $table) {
            $table->string('STOREID', 20)->primary();
            $table->string('NAME', 60)->nullable();
            $table->string('ADDRESS', 250)->nullable();
            $table->string('STREET', 250)->nullable();
            $table->string('ZIPCODE', 10)->nullable();
            $table->string('CITY', 60)->nullable();
            $table->string('STATE', 30)->nullable();
            $table->string('COUNTRY', 10)->nullable();
            $table->string('PHONE', 20)->nullable();
            $table->string('CURRENCY', 3)->nullable();
            $table->string('SQLSERVERNAME', 50)->nullable();
            $table->string('DATABASENAME', 50)->nullable();
            $table->string('USERNAME', 50)->nullable();
            $table->string('PASSWORD', 50)->nullable();
            $table->tinyInteger('WINDOWSAUTHENTICATION')->nullable();
            $table->string('FORMINFOFIELD1', 60)->nullable();
            $table->string('FORMINFOFIELD2', 60)->nullable();
            $table->string('FORMINFOFIELD3', 60)->nullable();
            $table->string('FORMINFOFIELD4', 60)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rbostoretables');
    }
};
