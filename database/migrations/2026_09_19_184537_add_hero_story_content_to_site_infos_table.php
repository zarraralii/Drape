<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->string('hero_eyebrow')->nullable()->after('hero_description');
            $table->string('hero_subheadline')->nullable()->after('hero_eyebrow');
            $table->string('hero_cta_label')->nullable()->after('hero_subheadline');
            $table->string('story_eyebrow')->nullable()->after('hero_cta_label');
            $table->string('story_headline')->nullable()->after('story_eyebrow');
            $table->text('story_description')->nullable()->after('story_headline');
            $table->string('story_cta_label')->nullable()->after('story_description');
        });
    }

    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn([
                'hero_eyebrow', 'hero_subheadline', 'hero_cta_label',
                'story_eyebrow', 'story_headline', 'story_description', 'story_cta_label',
            ]);
        });
    }
};
