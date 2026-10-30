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
            $table->renameColumn('promo_banner_center', 'promo_banner_full');
            $table->renameColumn('promo_banner_center_link', 'promo_banner_full_link');
        });

        Schema::table('site_infos', function (Blueprint $table) {
            $table->string('promo_banner_left_label')->nullable()->after('promo_banner_left_link');
            $table->string('promo_banner_full_label')->nullable()->after('promo_banner_full_link');
            $table->string('promo_banner_right_label')->nullable()->after('promo_banner_right_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn([
                'promo_banner_left_label',
                'promo_banner_full_label',
                'promo_banner_right_label',
            ]);
        });

        Schema::table('site_infos', function (Blueprint $table) {
            $table->renameColumn('promo_banner_full', 'promo_banner_center');
            $table->renameColumn('promo_banner_full_link', 'promo_banner_center_link');
        });
    }
};
