<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gcs_settings', function (Blueprint $table) {
            $table->id();
            $table->string('storage_driver')->default('local'); // 'local' or 'gcs'
            $table->string('gcs_bucket')->nullable();
            $table->string('gcs_project_id')->nullable();
            $table->text('gcs_key_file')->nullable(); // JSON credentials
            $table->timestamps();
        });

        Schema::create('design_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('print_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('design_categories')->nullOnDelete();
            $table->string('title');
            $table->string('image_path'); // local storage path or GCS path
            $table->string('status')->default('active'); // 'active' or 'inactive'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_designs');
        Schema::dropIfExists('design_categories');
        Schema::dropIfExists('gcs_settings');
    }
};
