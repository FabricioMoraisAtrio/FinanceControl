<?php

namespace App\Http\Controllers;

use App\Models\CreditCardBill;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $month = now()->month;
        $year  = now()->year;
        $today = now()->toDateString();

        // Saldo por tipo de conta
        $accounts       = $user->accounts()->where('active', true)->orderBy('name')->get();
        $totalBalance   = $accounts->whereNotIn('type', ['credit_card'])->sum('balance');
        $creditAccounts = $accounts->where('type', 'credit_card');

        // Faturas fechadas ainda não pagas (pagamento futuro agendado na conta corrente)
        $pendingBills = CreditCardBill::where('user_id', $user->id)
            ->where('status', 'closed')
            ->get();

        // Soma dos pagamentos futuros de faturas fechadas (transação com data > hoje)
        $futureBillPayments = (float) $user->transactions()
            ->expense()
            ->where('date', '>', $today)
            ->whereIn('id', $pendingBills->pluck('payment_transaction_id')->filter())
            ->sum('amount');

        // Faturas abertas (não fechadas) do ciclo atual
        $openCreditTotal = $creditAccounts->sum(fn($acc) => abs($acc->balance));

        // Saldo projetado = saldo real − faturas fechadas a pagar − faturas abertas
        $projectedBalance = $totalBalance - $futureBillPayments - $openCreditTotal;

        $creditBalance = $creditAccounts->sum('balance');

        // Saídas PREVISTAS: apenas despesas com data >= hoje (dias que ainda virão)

        $fixedExpenses = $user->transactions()
            ->expense()
            ->where('is_fixed', true)
            ->ofMonth($month, $year)
            ->where('date', '>=', $today)
            ->sum('amount');

        $installmentExpenses = $user->transactions()
            ->expense()
            ->whereNotNull('installment_total')
            ->ofMonth($month, $year)
            ->where('date', '>=', $today)
            ->sum('amount');

        $saídasPrevistas = $fixedExpenses + $installmentExpenses;

        // Totais do mês
        $monthlyIncome  = $user->transactions()->income()->ofMonth($month, $year)->sum('amount');
        $monthlyExpense = $user->transactions()->expense()->ofMonth($month, $year)->sum('amount');

        // Fatura do cartão de crédito este mês
        $creditCardExpense = $user->transactions()
            ->expense()
            ->ofMonth($month, $year)
            ->credit()
            ->sum('amount');

        $otherExpense = $monthlyExpense - $creditCardExpense;

        // Lançamentos recentes — parcelas agrupadas, 1 linha por grupo
        $rawRecent = $user->transactions()
            ->with(['account', 'category'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(40)
            ->get();

        $seenGroups = [];
        $recentTransactions = $rawRecent->filter(function ($t) use (&$seenGroups) {
            if ($t->installment_group_id) {
                if (in_array($t->installment_group_id, $seenGroups)) return false;
                $seenGroups[] = $t->installment_group_id;
            }
            return true;
        })->take(8)->values();

        // Próximas parcelas (a partir de hoje, inclusive)
        $upcomingInstallments = $user->transactions()
            ->expense()
            ->whereNotNull('installment_total')
            ->where('date', '>=', $today)
            ->with(['account', 'category'])
            ->orderBy('date')
            ->limit(8)
            ->get();

        // Orçamentos do mês
        $budgets = $user->budgets()
            ->with('category')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        return view('dashboard', compact(
            'totalBalance', 'creditBalance', 'creditAccounts',
            'projectedBalance', 'futureBillPayments', 'openCreditTotal',
            'saídasPrevistas', 'fixedExpenses', 'installmentExpenses',
            'monthlyIncome', 'monthlyExpense',
            'creditCardExpense', 'otherExpense',
            'recentTransactions', 'accounts', 'budgets',
            'upcomingInstallments', 'month', 'year'
        ));
    }
}
