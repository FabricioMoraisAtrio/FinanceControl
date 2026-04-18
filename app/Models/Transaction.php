<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'account_to_id',
        'type',
        'amount',
        'description',
        'date',
        'notes',
        'reconciled',
        'is_fixed',
        'installment_group_id',
        'installment_current',
        'installment_total',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'date'                => 'date',
        'reconciled'          => 'boolean',
        'is_fixed'            => 'boolean',
        'installment_current' => 'integer',
        'installment_total'   => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function accountTo(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_to_id');
    }

    public function isInstallment(): bool
    {
        return !is_null($this->installment_total) && $this->installment_total > 1;
    }

    public function installmentLabel(): string
    {
        if (!$this->isInstallment()) return '';
        return "{$this->installment_current}/{$this->installment_total}";
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'income'   => 'Entrada',
            'expense'  => 'Saída',
            'transfer' => 'Transferência',
            default    => $this->type,
        };
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeOfMonth($query, int $month, int $year)
    {
        return $query->whereMonth('date', $month)->whereYear('date', $year);
    }

    public function scopeDebit($query)
    {
        return $query->whereHas('account', fn($q) => $q->whereIn('type', ['checking', 'savings', 'cash', 'investment']));
    }

    public function scopeCredit($query)
    {
        return $query->whereHas('account', fn($q) => $q->where('type', 'credit_card'));
    }
}
