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
            $table->string('state')->nullable()->after('city');
            $table->decimal('shipping_charge', 10, 2)->nullable()->after('discount');
            $table->timestamp('delivered_at')->nullable()->after('status');
            $table->text('cancel_reason')->nullable()->after('delivered_at');
            
            // Shiprocket fields
            $table->string('shiprocket_order_id')->nullable()->after('cancel_reason');
            $table->string('shiprocket_shipment_id')->nullable()->after('shiprocket_order_id');
            $table->string('shiprocket_awb_code')->nullable()->after('shiprocket_shipment_id');
            $table->string('shiprocket_courier_name')->nullable()->after('shiprocket_awb_code');
            $table->text('shiprocket_tracking_url')->nullable()->after('shiprocket_courier_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'state',
                'shipping_charge',
                'delivered_at',
                'cancel_reason',
                'shiprocket_order_id',
                'shiprocket_shipment_id',
                'shiprocket_awb_code',
                'shiprocket_courier_name',
                'shiprocket_tracking_url'
            ]);
        });
    }
};
