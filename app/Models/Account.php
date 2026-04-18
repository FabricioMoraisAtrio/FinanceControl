<?php

namespace App\Models;

use App\Models\CreditCardBill;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'balance',
        'initial_balance',
        'closing_day',
        'payment_day',
        'payment_account_id',
        'credit_limit',
        'color',
        'icon',
        'active',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'credit_limit'    => 'decimal:2',
        'active'          => 'boolean',
        'closing_day'     => 'integer',
        'payment_day'     => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_to_id');
    }

    /**
     * Saldo real: considera apenas transações com data <= hoje.
     * Transações futuras (ex: pagamento de fatura agendado) não reduzem o saldo atual.
     * Para cartões de crédito, retorna o valor negativo da fatura do ciclo atual.
     */
    public function getBalanceAttribute(): float
    {
        if ($this->type === 'credit_card') {
            return $this->getOpenBillAmount();
        }

        $today       = now()->toDateString();
        $income      = (float) $this->transactions()->where('type', 'income')->where('date', '<=', $today)->sum('amount');
        $expense     = (float) $this->transactions()->where('type', 'expense')->where('date', '<=', $today)->sum('amount');
        $transferIn  = (float) $this->transfersTo()->where('type', 'transfer')->where('date', '<=', $today)->sum('amount');
        $transferOut = (float) $this->transactions()->where('type', 'transfer')->where('date', '<=', $today)->sum('amount');

        return (float) $this->attributes['initial_balance'] + $income - $expense + $transferIn - $transferOut;
    }

    /**
     * Valor total em aberto no cartão: tudo que não foi incluído em fatura fechada.
     * Considera compras desde o dia seguinte ao fechamento da última fatura (ou desde sempre).
     */
    public function getOpenBillAmount(): float
    {
        $lastBill = \App\Models\CreditCardBill::where('credit_account_id', $this->id)
            ->orderByDesc('period_end')
            ->first();

        $fromDate = $lastBill
            ? $lastBill->period_end->copy()->addDay()->format('Y-m-d')
            : '2000-01-01';

        return -(float) $this->transactions()
            ->where('type', 'expense')
            ->where('date', '>=', $fromDate)
            ->where('date', '<=', now()->toDateString())
            ->sum('amount');
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->transactions()->where('type', 'expense')->sum('amount');
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'checking'    => 'Conta Corrente',
            'savings'     => 'Poupança',
            'cash'        => 'Dinheiro',
            'investment'  => 'Investimento',
            'credit_card' => 'Cartão de Crédito',
            default       => $this->type,
        };
    }
}
