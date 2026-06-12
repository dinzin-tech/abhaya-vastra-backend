<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('coupon_user', function (Blueprint $table) {
            $table->timestamp('used_at')->nullable()->after('used');
        });
    }

    public function down()
    {
        Schema::table('coupon_user', function (Blueprint $table) {
            $table->dropColumn('used_at');
        });
    }
};
