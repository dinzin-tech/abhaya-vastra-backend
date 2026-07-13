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
        // Add fields to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->string('qikink_order_id')->nullable()->after('shiprocket_tracking_url');
            $table->string('qikink_status')->nullable()->after('qikink_order_id');
            $table->boolean('qikink_shipping')->default(true)->after('qikink_status');
            $table->timestamp('qikink_sent_at')->nullable()->after('qikink_shipping');
            $table->string('qikink_awb_code')->nullable()->after('qikink_sent_at');
            $table->text('qikink_tracking_url')->nullable()->after('qikink_awb_code');
        });

        // Add fields to products table
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_qikink_product')->default(false)->after('customizable');
            $table->string('qikink_sku')->nullable()->after('is_qikink_product');
            $table->integer('qikink_print_type_id')->default(1)->after('qikink_sku'); // Default 1 for DTG
            $table->boolean('search_from_my_products')->default(false)->after('qikink_print_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'qikink_order_id',
                'qikink_status',
                'qikink_shipping',
                'qikink_sent_at',
                'qikink_awb_code',
                'qikink_tracking_url'
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_qikink_product',
                'qikink_sku',
                'qikink_print_type_id',
                'search_from_my_products'
            ]);
        });
    }
};
