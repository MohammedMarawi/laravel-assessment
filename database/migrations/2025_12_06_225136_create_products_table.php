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
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('file_path')->nullable();
            $table->softDeletes(); // adds deleted_at column
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
