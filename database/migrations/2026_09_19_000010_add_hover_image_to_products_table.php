<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The storefront's product cards crossfade to a second "hover" image —
     * not part of the original template, added for that.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('hover_image')->nullable()->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('hover_image');
        });
    }
};
