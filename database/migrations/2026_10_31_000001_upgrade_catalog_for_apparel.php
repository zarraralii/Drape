<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Apparel catalogue: collections get their own URL handle, option values
 * (sizes, colours) get a swatch + order, products get material & care
 * copy, and a variant's price becomes optional (blank = product price).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->unsignedInteger('sort_order')->default(0)->after('is_active');
        });

        $used = [];
        foreach (DB::table('categories')->orderBy('id')->get(['id', 'name']) as $category) {
            $base = Str::slug((string) $category->name) ?: 'collection';
            $slug = $base;
            for ($i = 2; in_array($slug, $used, true) || $slug === 'all'; $i++) {
                $slug = $base . '-' . $i;
            }
            $used[] = $slug;
            DB::table('categories')->where('id', $category->id)->update(['slug' => $slug]);
        }

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->string('swatch', 20)->nullable()->after('value');
            $table->unsignedInteger('sort_order')->default(0)->after('swatch');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->text('material_care')->nullable()->after('description');
            $table->index(['is_active', 'category_id']);
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->integer('quantity')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->nullable(false)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'category_id']);
            $table->dropColumn('material_care');
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropColumn(['swatch', 'sort_order']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'description', 'sort_order']);
        });
    }
};
