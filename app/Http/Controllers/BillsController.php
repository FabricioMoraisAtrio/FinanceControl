<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CreditCardBill;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BillsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Todos os cartões de crédito ativos
        $creditCards = $user->accounts()
            ->where('type', 'credit_card')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Para cada cartão, calcula o período atual e monta o resumo
        $cards = $creditCards->map(function (Account $account) use ($user) {
            $closingDay = $account->closing_day ?? 21;
            $today      = now();

            // Período atual
            if ($today->day <= $closingDay) {
                $periodEnd   = $today->copy()->startOfMonth()->addDays($closingDay - 1);
                $periodStart = $periodEnd->copy()->subMonth()->addDay();
            } else {
                $periodStart = $today->copy()->startOfMonth()->addDays($closingDay);
                $periodEnd   = $periodStart->copy()->addMonth()->subDay();
            }

            $paymentDay = $account->payment_day ?? 10;
            $dueDate    = $periodEnd->copy()->addMonth()->startOfMonth()->addDays($paymentDay - 1);

            // Total gasto no período atual
            $currentTotal = (float) Transaction::where('account_id', $account->id)
                ->where('type', 'expense')
                ->whereBetween('date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
                ->sum('amount');

            // Última fatura fechada
            $lastBill = CreditCardBill::where('credit_account_id', $account->id)
                ->orderByDesc('period_end')
                ->with('paymentAccount')
                ->first();

            // Total histórico: soma de todas as despesas na conta (todos os tempos)
            $totalAllTime = (float) Transaction::where('account_id', $account->id)
                ->where('type', 'expense')
                ->sum('amount');

            // Lançamentos do período atual (todos, para o card recolhível)
            $recentTransactions = Transaction::where('account_id', $account->id)
                ->where('type', 'expense')
                ->whereBetween('date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
                ->with('category')
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();

            return [
                'account'             => $account,
                'period_start'        => $periodStart,
                'period_end'          => $periodEnd,
                'due_date'            => $dueDate,
                'current_total'       => $currentTotal,
                'total_all_time'      => $totalAllTime,
                'last_bill'           => $lastBill,
                'recent_transactions' => $recentTransactions,
                'days_to_close'       => (int) $today->diffInDays($periodEnd, false),
            ];
        });

        return view('bills.index', compact('cards'));
    }
}

