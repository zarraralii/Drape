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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("order_id")->nullable();
            $table->unsignedBigInteger("item_id")->nullable();
            $table->json("item_options")->nullable();
            $table->decimal("item_total", 8, 2)->default(0)->nullable();
            $table->decimal("discount", 8, 2)->default(0)->nullable();
            $table->integer("qty")->nullable();
            $table->text("remarks")->nullable();
            $table->date("date")->nullable();
            $table->string("status")->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('no action');
            $table->foreign('item_id')->references('id')->on('products')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
