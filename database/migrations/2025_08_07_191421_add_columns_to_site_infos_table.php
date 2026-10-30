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
            $table->string('contact_banner')->after('youtube_link')->nullable();
            $table->string('about_banner')->after('contact_banner')->nullable();
            $table->string('tnc_banner')->after('about_banner')->nullable();
            $table->string('faq_banner')->after('tnc_banner')->nullable();
            $table->string('checkout_banner')->after('faq_banner')->nullable();
            $table->string('cart_banner')->after('checkout_banner')->nullable();
            $table->string('favorite_banner')->after('cart_banner')->nullable();
            $table->string('shop_banner')->after('favorite_banner')->nullable();
            $table->string('privacy_banner')->after('shop_banner')->nullable();
            $table->string('return_banner')->after('privacy_banner')->nullable();
            $table->string('account_banner')->after('return_banner')->nullable();
            $table->string('home_banner')->after('account_banner')->nullable();
            $table->text('return_and_exchange')->after('home_banner')->nullable();
            $table->text('free_shipping_quote')->after('return_and_exchange')->nullable();
            $table->text('gallery_quote')->after('free_shipping_quote')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            //
        });
    }
};
