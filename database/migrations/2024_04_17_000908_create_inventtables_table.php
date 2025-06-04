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
        Schema::create('inventtables', function (Blueprint $table) {
            $table->string('itemgroupid', 20)->default('');
            $table->string('itemid', 20)->default('');
            $table->string('itemname', 60)->nullable();
            $table->unsignedTinyInteger('itemtype')->default(0); 
            $table->string('namealias', 60)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Added primary key constraint separately
            $table->primary(['itemid']);
            
            // Removed 'timestamps' as they're not needed
            
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4'; // Changed charset to utf8mb4
            $table->collation = 'utf8mb4_unicode_ci'; // Changed collation to utf8mb4_unicode_ci
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventtables');
    }
};

