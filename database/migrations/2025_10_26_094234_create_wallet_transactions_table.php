<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');

            $table->enum('type', ['credit', 'debit']); // transaction direction
            $table->integer('points'); // number of points

            $table->enum('status', ['pending', 'completed', 'reversed'])->default('pending');

            $table->string('description')->nullable();
            $table->string('reference')->nullable(); // e.g., ORDER-123
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
