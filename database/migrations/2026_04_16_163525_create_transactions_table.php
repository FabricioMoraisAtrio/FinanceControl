<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_to_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->enum('type', ['income', 'expense', 'transfer'])->default('expense');
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->boolean('reconciled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
