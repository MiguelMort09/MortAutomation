<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_customers', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_products', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_prices', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique();
            $table->foreignId('stripe_product_id')->constrained('stripe_products')->cascadeOnDelete();
            $table->integer('amount');
            $table->string('currency', 3);
            $table->string('interval')->nullable(); // day, week, month, year
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_prices');
        Schema::dropIfExists('stripe_products');
        Schema::dropIfExists('stripe_customers');
    }
};
