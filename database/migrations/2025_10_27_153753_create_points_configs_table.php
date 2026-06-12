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
        Schema::create('points_configs', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_amount', 10, 2)->comment('Minimum amount to purchase');
            $table->decimal('max_amount', 10, 2)->nullable()->comment('Maximum amount (optional)');
            $table->integer('points')->comment('Points user will receive');
            $table->decimal('coin_value', 10, 2)->default(1)->comment('Value of 1 point in rupees');
            $table->boolean('status')->default(1)->comment('Active/Inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_configs');
    }
};
