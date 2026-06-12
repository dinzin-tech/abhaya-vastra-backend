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
        Schema::create('reward_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_order_value', 10, 2)->default(100)->comment('Minimum order value to earn points');
            $table->decimal('reward_base_amount', 10, 2)->default(100)->comment('Base amount for points calculation');
            $table->integer('reward_points')->default(1)->comment('Points awarded per base amount');
            $table->decimal('points_value', 10, 2)->default(1)->comment('Value of 1 point in rupees');
            $table->boolean('status')->default(1)->comment('Active/Inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_settings');
    }
};
