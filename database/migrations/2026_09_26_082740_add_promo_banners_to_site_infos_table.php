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
        Schema::table('site_infos', function (Blueprint $table) {
            $table->string('promo_banner_left')->nullable();
            $table->string('promo_banner_left_link')->nullable();
            $table->string('promo_banner_center')->nullable();
            $table->string('promo_banner_center_link')->nullable();
            $table->string('promo_banner_right')->nullable();
            $table->string('promo_banner_right_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn([
                'promo_banner_left',
                'promo_banner_left_link',
                'promo_banner_center',
                'promo_banner_center_link',
                'promo_banner_right',
                'promo_banner_right_link',
            ]);
        });
    }
};
