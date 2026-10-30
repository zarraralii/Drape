<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Store-wide settings for the Drape storefront: announcement bar, free
 * shipping threshold, currency label, size guide and shipping policy.
 * The perfume-bottle artwork columns are no longer used anywhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->string('announcement_text')->nullable()->after('tagline');
            $table->string('currency', 10)->default('PKR')->after('announcement_text');
            $table->decimal('free_shipping_threshold', 10, 2)->nullable()->after('delivery_charges');
            $table->text('size_guide')->nullable();
            $table->text('shipping_policy')->nullable();
        });

        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn(['bottle_body_image', 'bottle_cap_image']);
        });

        DB::table('site_infos')->where('brand_name', 'SAFA ALSALAM')->update(['brand_name' => 'Drape']);
    }

    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->string('bottle_body_image')->nullable();
            $table->string('bottle_cap_image')->nullable();
        });

        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn(['announcement_text', 'currency', 'free_shipping_threshold', 'size_guide', 'shipping_policy']);
        });
    }
};
