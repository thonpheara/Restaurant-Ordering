<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            $table->string('name');
            $table->string('code')->unique()->nullable(); 
            $table->integer('qty')->default(0);
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 10, 2);
            $table->integer('count')->default(0);
            $table->text('description')->nullable();
            $table->json('images')->nullable(); 
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
