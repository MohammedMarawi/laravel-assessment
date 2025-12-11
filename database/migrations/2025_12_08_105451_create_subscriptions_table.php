<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])
                ->default('pending')
                ->index();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();

            $table->string('stripe_subscription_id')->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'status']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
