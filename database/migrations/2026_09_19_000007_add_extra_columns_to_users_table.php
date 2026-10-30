<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * create_users_table already declares phone/profile_image/is_active/
     * softDeletes directly (added there after this migration was
     * originally written) — guarded so this stays a safe no-op on a
     * fresh install instead of failing on "column already exists".
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->tinyInteger('is_active')->default(1)->nullable()->after('profile_image');
            }
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        // No-op: the columns this migration would drop are owned by
        // create_users_table's own down() (Schema::dropIfExists), not
        // this migration, since it never truly added them itself.
    }
};
