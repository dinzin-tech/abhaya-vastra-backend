<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_exchanges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();

            $table->string('original_size')->nullable();
            $table->string('original_color')->nullable();
            $table->string('exchange_size')->nullable();
            $table->string('exchange_color')->nullable();

            $table->text('reason')->nullable();
            $table->json('images')->nullable();

            $table->decimal('exchange_charge', 10, 2)->default(0.00);
            $table->string('status')->default('pending');

            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_order_id')->nullable();
            $table->string('payment_status')->default('unpaid');

            $table->text('admin_note')->nullable();
            $table->timestamp('admin_updated_at')->nullable();

            // Shiprocket Pickup Details
            $table->string('shiprocket_pickup_order_id')->nullable();
            $table->string('shiprocket_pickup_shipment_id')->nullable();
            $table->string('shiprocket_pickup_awb_code')->nullable();
            $table->string('shiprocket_pickup_courier_name')->nullable();
            $table->timestamp('pickup_scheduled_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();

            // Shiprocket Delivery Details
            $table->string('shiprocket_delivery_order_id')->nullable();
            $table->string('shiprocket_delivery_shipment_id')->nullable();
            $table->string('shiprocket_delivery_awb_code')->nullable();
            $table->string('shiprocket_delivery_courier_name')->nullable();
            $table->timestamp('delivery_scheduled_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            // Optional foreign keys (uncomment if needed)
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_exchanges');
    }
};
