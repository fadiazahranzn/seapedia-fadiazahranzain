<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN or DROP FOREIGN KEY directly.
        // We recreate the table with product_id nullable + nullOnDelete.
        Schema::create('order_items_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 200);
            $table->decimal('product_price', 15, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 15, 2);
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement('INSERT INTO order_items_new SELECT * FROM order_items');

        Schema::drop('order_items');
        Schema::rename('order_items_new', 'order_items');
    }

    public function down(): void
    {
        Schema::create('order_items_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name', 200);
            $table->decimal('product_price', 15, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 15, 2);
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement('INSERT INTO order_items_old SELECT * FROM order_items');

        Schema::drop('order_items');
        Schema::rename('order_items_old', 'order_items');
    }
};
