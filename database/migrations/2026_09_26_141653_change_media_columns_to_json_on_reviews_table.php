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
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['media', 'media_type']);
        });

        // Multiple attachments per review now — one JSON array column
        // (each entry `{file, type}`) instead of the single filename +
        // type pair, added in a second step since SQLite can't add and
        // drop columns in the same ALTER batch.
        Schema::table('reviews', function (Blueprint $table) {
            $table->json('media')->nullable()->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('media');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->string('media')->nullable()->after('rating');
            $table->string('media_type', 10)->nullable()->after('media');
        });
    }
};
