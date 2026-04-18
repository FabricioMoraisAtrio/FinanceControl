<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCardBill extends Model
{
    protected $fillable = [
        'user_id',
        'credit_account_id',
        'payment_account_id',
        'payment_transaction_id',
        'period_start',
        'period_end',
        'due_date',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'due_date'     => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'credit_account_id');
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'payment_transaction_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool   { return $this->status === 'paid'; }
    public function isClosed(): bool { return $this->status === 'closed'; }
    public function isOpen(): bool   { return $this->status === 'open'; }
}
