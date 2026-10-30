<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name'); // snapshot, survives product deletion
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2); // price the agent paid/sold at
            $table->decimal('total_amount', 10, 2); // quantity * unit_price
            $table->decimal('commission_rate', 5, 2); // % snapshot at time of sale
            $table->decimal('commission_amount', 10, 2); // computed payout owed to the agent
            $table->date('sale_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_sales');
    }
};
