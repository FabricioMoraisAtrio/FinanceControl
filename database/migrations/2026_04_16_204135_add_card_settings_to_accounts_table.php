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
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedTinyInteger('closing_day')->default(21)->after('initial_balance');
            $table->unsignedTinyInteger('payment_day')->default(10)->after('closing_day');
            $table->unsignedBigInteger('payment_account_id')->nullable()->after('payment_day');
            $table->foreign('payment_account_id')->references('id')->on('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['payment_account_id']);
            $table->dropColumn(['closing_day', 'payment_day', 'payment_account_id']);
        });
    }
};
