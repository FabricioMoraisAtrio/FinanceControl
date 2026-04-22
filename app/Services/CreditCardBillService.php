<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CreditCardBill;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreditCardBillService
{
    /**
     * Fecha períodos encerrados sem fatura e marca como pagas as faturas vencidas.
     * Seguro para chamar em qualquer request — verifica antes de criar duplicatas.
     */
    public function processAccount(Account $account): void
    {
        $today      = now();
        $closingDay = $account->closing_day ?? 21;
        $paymentDay = $account->payment_day ?? 10;

        // ── Corrige faturas inconsistentes com a data atual ───────────────────

        // Remove faturas cujo período ainda não encerrou (criadas com data futura)
        CreditCardBill::where('credit_account_id', $account->id)
            ->where('period_end', '>', $today->format('Y-m-d'))
            ->get()
            ->each(function (CreditCardBill $bill) {
                if ($bill->payment_transaction_id) {
                    Transaction::find($bill->payment_transaction_id)?->delete();
                }
                $bill->delete();
            });

        // Reverte para "fechada" faturas marcadas como pagas antes do vencimento
        CreditCardBill::where('credit_account_id', $account->id)
            ->where('status', 'paid')
            ->where('due_date', '>', $today->format('Y-m-d'))
            ->update(['status' => 'closed']);

        // ── Fecha períodos encerrados ─────────────────────────────────────────

        for ($i = 1; $i <= 12; $i++) {
            if ($today->day > $closingDay) {
                $periodEnd = Carbon::create($today->year, $today->month, $closingDay)->subMonths($i - 1);
            } else {
                $periodEnd = Carbon::create($today->year, $today->month, $closingDay)->subMonths($i);
            }

            if ($periodEnd->gt($today)) {
                continue;
            }

            $periodStart = $periodEnd->copy()->subMonth()->addDay();
            $dueDate     = $periodEnd->copy()->addMonth()->startOfMonth()->addDays($paymentDay - 1);

            $exists = CreditCardBill::where('credit_account_id', $account->id)
                ->where('period_end', $periodEnd->format('Y-m-d'))
                ->exists();

            if ($exists) {
                continue;
            }

            $total = (float) Transaction::where('account_id', $account->id)
                ->where('type', 'expense')
                ->whereBetween('date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
                ->sum('amount');

            if ($total <= 0) {
                continue;
            }

            DB::transaction(function () use ($account, $periodStart, $periodEnd, $dueDate, $total) {
                $paymentAccountId = $account->payment_account_id;
                $paymentTxId      = null;

                if ($paymentAccountId && Account::find($paymentAccountId)) {
                    $tx = Transaction::create([
                        'user_id'     => $account->user_id,
                        'type'        => 'expense',
                        'account_id'  => $paymentAccountId,
                        'category_id' => null,
                        'amount'      => $total,
                        'description' => 'Fatura ' . $account->name . ' (' . $periodEnd->format('m/Y') . ')',
                        'date'        => $dueDate->format('Y-m-d'),
                        'notes'       => 'Débito automático de fatura.',
                        'is_fixed'    => false,
                    ]);
                    $paymentTxId = $tx->id;
                }

                CreditCardBill::create([
                    'user_id'                => $account->user_id,
                    'credit_account_id'      => $account->id,
                    'payment_account_id'     => $paymentAccountId,
                    'payment_transaction_id' => $paymentTxId,
                    'period_start'           => $periodStart,
                    'period_end'             => $periodEnd,
                    'due_date'               => $dueDate,
                    'total_amount'           => $total,
                    'status'                 => 'closed',
                ]);
            });
        }

        // Marca como pagas faturas fechadas com vencimento já expirado
        CreditCardBill::where('credit_account_id', $account->id)
            ->where('status', 'closed')
            ->where('due_date', '<=', $today->format('Y-m-d'))
            ->update(['status' => 'paid']);
    }

    /**
     * Processa todos os cartões de crédito de um usuário.
     */
    public function processUserCards(int $userId): void
    {
        Account::where('user_id', $userId)
            ->where('type', 'credit_card')
            ->where('active', true)
            ->each(fn(Account $card) => $this->processAccount($card));
    }
}
