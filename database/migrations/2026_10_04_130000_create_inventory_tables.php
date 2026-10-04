<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained();
            $t->string('name');
            $t->string('sku')->unique();
            $t->unsignedInteger('price_cents');
            $t->unsignedInteger('stock')->default(0);
            $t->unsignedInteger('minimum_stock')->default(5);
            $t->timestamps();
        });
        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained();
            $t->string('customer');
            $t->unsignedInteger('total_cents');
            $t->timestamps();
        });
        Schema::create('sale_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sale_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->unsignedInteger('quantity');
            $t->unsignedInteger('unit_price_cents');
            $t->timestamps();
        });
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->integer('quantity');
            $t->string('reason');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['stock_movements', 'sale_items', 'sales', 'products', 'categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
