<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            // How this agent works with us: buys stock wholesale to resell
            // themselves, or simply refers customers to buy directly here.
            $table->enum('type', ['vendor', 'referrer'])->default('vendor');
            $table->decimal('commission_rate', 5, 2)->default(10); // default % used when logging a sale
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
