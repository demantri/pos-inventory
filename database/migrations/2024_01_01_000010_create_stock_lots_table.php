<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            // FK ke goods_receipts (sudah ada di migration 8) — aman
            $table->foreignId('goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
            $table->string('lot_number', 50)->unique();
            $table->date('received_date');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('qty_initial', 10, 2);
            $table->decimal('qty_remaining', 10, 2);
            $table->boolean('is_exhausted')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'is_exhausted', 'received_date']);
            $table->index('lot_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_lots');
    }
};
