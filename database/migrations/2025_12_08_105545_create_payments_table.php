<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('transaction_id')->unique();
            $table->string('stripe_session_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('usd');

            $table->enum('status', ['unpaid', 'paid', 'failed', 'refunded'])
                ->default('unpaid')
                ->index();

            $table->text('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['user_id', 'status']);
            $table->index(['subscription_id', 'status']);
            $table->index('created_at');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
