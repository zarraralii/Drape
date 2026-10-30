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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('uid')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal("delivery_charges", 8, 2)->default(0)->nullable();
            $table->string('status')->nullable();
            $table->string("rejection_reason")->nullable();
            $table->string("proof_of_delivery")->nullable();
            $table->string("invoice_pdf")->nullable();
            $table->json("cart")->nullable();
            $table->string('payment_method')->nullable();
            $table->text("remarks")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
