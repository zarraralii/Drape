<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->string('bottle_body_image')->nullable()->after('logo');
            $table->string('bottle_cap_image')->nullable()->after('bottle_body_image');
        });
    }

    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn(['bottle_body_image', 'bottle_cap_image']);
        });
    }
};
