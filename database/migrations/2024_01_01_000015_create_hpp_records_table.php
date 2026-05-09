<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hpp_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained('sale_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->date('period_date');
            $table->decimal('total_qty_sold', 10, 2);
            $table->decimal('sale_revenue', 15, 2);
            $table->decimal('total_hpp', 15, 2);
            $table->decimal('avg_hpp_per_unit', 15, 4);
            $table->decimal('gross_profit', 15, 2);
            $table->decimal('gross_margin_percent', 8, 4);
            $table->timestamps();

            $table->index(['period_date', 'product_id']);
            $table->index('sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hpp_records');
    }
};
