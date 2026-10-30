<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Orders keep their own copy of the customer's contact details (so guest
 * checkout never has to create or overwrite a user account), and each
 * line remembers which variant — size / colour — was bought. restocked_at
 * marks a cancelled order whose stock has already been put back.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('user_id');
            $table->string('customer_phone', 30)->nullable()->after('customer_name');
            $table->string('customer_email')->nullable()->after('customer_phone');
            $table->timestamp('restocked_at')->nullable()->after('status');
            $table->index('uid');
            $table->index('customer_phone');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('variation_id')->nullable()->after('item_id');
            $table->string('product_name')->nullable()->after('variation_id');
            $table->string('variant_label')->nullable()->after('product_name');
            $table->string('sku')->nullable()->after('variant_label');
            $table->decimal('unit_price', 10, 2)->nullable()->after('sku');
        });

        foreach (DB::table('orders')->whereNotNull('user_id')->get(['id', 'user_id']) as $order) {
            $user = DB::table('users')->where('id', $order->user_id)->first(['name', 'phone', 'email']);
            if ($user) {
                DB::table('orders')->where('id', $order->id)->update([
                    'customer_name' => $user->name,
                    'customer_phone' => $user->phone,
                    'customer_email' => $user->email,
                ]);
            }
        }

        DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.item_id')
            ->whereNull('order_items.product_name')
            ->select('order_items.id', 'products.name')
            ->get()
            ->each(fn ($row) => DB::table('order_items')->where('id', $row->id)->update(['product_name' => $row->name]));
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['variation_id', 'product_name', 'variant_label', 'sku', 'unit_price']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['uid']);
            $table->dropIndex(['customer_phone']);
            $table->dropColumn(['customer_name', 'customer_phone', 'customer_email', 'restocked_at']);
        });
    }
};
