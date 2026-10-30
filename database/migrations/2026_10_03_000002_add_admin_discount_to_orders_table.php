<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The part of `discount_amount` an admin typed in by hand when creating
     * the order (the rest, if any, came from the coupon). `discount_amount`
     * stays the order's full discount, so totals elsewhere don't change.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('admin_discount', 10, 2)->default(0)->after('discount_amount');
            $table->string('admin_discount_type', 10)->nullable()->after('admin_discount');
            $table->decimal('admin_discount_value', 10, 2)->nullable()->after('admin_discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['admin_discount', 'admin_discount_type', 'admin_discount_value']);
        });
    }
};
