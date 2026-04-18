<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Parcelamentos
            $table->string('installment_group_id', 36)->nullable()->after('is_fixed'); // UUID do grupo
            $table->unsignedTinyInteger('installment_current')->nullable()->after('installment_group_id'); // parcela atual (ex: 2)
            $table->unsignedTinyInteger('installment_total')->nullable()->after('installment_current');   // total de parcelas (ex: 6)
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['installment_group_id', 'installment_current', 'installment_total']);
        });
    }
};
