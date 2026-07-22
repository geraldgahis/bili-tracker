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
        Schema::create('user_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            
            // Personal custom name override (e.g., user wants to display "Coke Small" instead of "Coca-Cola Original Taste 80ml")
            $table->string('custom_name')->nullable(); 
            
            // Piece vs. Bulk packaging tracking
            $table->string('purchase_unit')->default('piece'); // e.g., piece, pack, case, rim, box
            $table->integer('pieces_per_bulk')->default(1); // e.g., 1 for piece, 24 for case/box, 12 for dozen pack
            
            // User-specific price (in PHP)
            $table->decimal('price', 10, 2)->default(0.00);
            
            $table->boolean('is_tracked')->default(true);
            $table->text('user_notes')->nullable();
            
            $table->timestamps();

            // Unique index prevents duplicate entries for identical combinations
            $table->unique([
                'user_id', 
                'product_id', 
                'store_id', 
                'purchase_unit', 
                'pieces_per_bulk'
            ], 'user_prod_store_unit_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_products');
    }
};
