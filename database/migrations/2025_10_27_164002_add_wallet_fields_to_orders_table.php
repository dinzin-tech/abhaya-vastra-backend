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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('wallet_money_used', 10, 2)->default(0)->after('total')->comment('Wallet money used in this order');
            $table->integer('loyalty_points_used')->default(0)->after('wallet_money_used')->comment('Loyalty points used in this order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['wallet_money_used', 'loyalty_points_used']);
        });
    }
};
