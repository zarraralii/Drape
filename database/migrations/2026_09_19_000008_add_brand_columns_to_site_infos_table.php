<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original template's site_infos table covers policy pages,
     * contact details, and section banners, but has no notion of a brand
     * identity — this perfume site needs one (nav logo text, hero copy).
     */
    public function up(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->string('brand_name')->default('Drape')->after('id');
            $table->string('tagline')->nullable()->after('brand_name');
            $table->text('hero_description')->nullable()->after('tagline');
        });
    }

    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn(['brand_name', 'tagline', 'hero_description']);
        });
    }
};
