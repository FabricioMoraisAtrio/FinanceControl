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
        Schema::create('credit_card_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('credit_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('payment_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->date('period_start');
            $table->date('period_end');    // data de fechamento
            $table->date('due_date');      // data de vencimento (dia 10)
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['open', 'closed', 'paid'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_card_bills');
    }
};
