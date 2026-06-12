<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('user_id')->index();

            $table->text('reason')->nullable();
            $table->json('images')->nullable();

            $table->string('tracking_id')->nullable();
            $table->string('status')->default('pending');

            $table->timestamp('delivered_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('admin_updated_at')->nullable();

            $table->boolean('refund_processed')->default(false);
            $table->decimal('refund_amount', 10, 2)->default(0.00);
            $table->string('refund_id')->nullable();
            $table->timestamp('refund_received_at')->nullable();

            // Shiprocket Return Details
            $table->string('shiprocket_return_order_id')->nullable();
            $table->string('shiprocket_return_shipment_id')->nullable();
            $table->string('shiprocket_return_awb_code')->nullable();
            $table->string('shiprocket_return_courier_name')->nullable();
            $table->timestamp('return_pickup_scheduled_at')->nullable();

            $table->timestamps();

            // Optional foreign keys
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_returns');
    }
};
