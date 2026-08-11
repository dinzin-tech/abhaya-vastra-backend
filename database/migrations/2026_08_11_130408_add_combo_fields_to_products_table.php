<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_combo')->default(false)->after('gender');
            $table->string('combo_type')->nullable()->after('is_combo'); // 'male_female', 'siblings', 'couple'
            $table->json('male_sizes')->nullable()->after('combo_type');   // ["S","M","L","XL","XXL"]
            $table->json('female_sizes')->nullable()->after('male_sizes'); // ["XS","S","M","L","XL"]
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_combo', 'combo_type', 'male_sizes', 'female_sizes']);
        });
    }
};
