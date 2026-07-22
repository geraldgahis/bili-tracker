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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique()->index(); 
            $table->string('name'); 
            $table->text('description')->nullable();
            $table->string('size')->nullable(); 
            
            // Product Image
            $table->string('image_path')->nullable(); 
            
            // ADD THIS: Barcode proof image uploaded by the user
            $table->string('barcode_image_path')->nullable(); 
            
            // ADD THIS: Moderation status (pending, approved, rejected)
            $table->string('status')->default('pending'); 
            
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
