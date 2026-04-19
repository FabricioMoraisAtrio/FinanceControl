<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CreditCardBill;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditCardBillController extends Controller
{
    public function index(Account $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        abort_if($account->type !== 'credit_card', 422);

        $bills = CreditCardBill::where('credit_account_id', $account->id)
            ->orderByDesc('period_end')
            ->with(['paymentAccount', 'paymentTransaction'])
            ->get();

        $preview = $this->buildCurrentPeriodPreview($account);

        $checkingAccounts = Auth::user()->accounts()
            ->whereNotIn('type', ['credit_card'])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Transações do período atual
        $periodTransactions = Transaction::where('account_id', $account->id)
            ->where('type', 'expense')
            ->whereBetween('date', [
                $preview['period_start']->format('Y-m-d'),
                $preview['period_end']->format('Y-m-d'),
            ])
            ->with('category')
            ->orderBy('date')
            ->get();

        return view('credit_card_bills.index', compact(
            'account', 'bills', 'preview', 'checkingAccounts', 'periodTransactions'
        ));
    }

    public function close(Request $request, Account $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        abort_if($account->type !== 'credit_card', 422);

        $request->validate([
            'payment_account_id' => 'required|exists:accounts,id',
            'due_date'           => 'required|date',
        ]);

        $preview = $this->buildCurrentPeriodPreview($account);
        $total   = $preview['total_amount'];

        if ($total <= 0) {
            return back()->with('error', 'Não há gastos no período para fechar a fatura.');
        }

        // Verifica se já existe fatura fechada para este período
        $existing = CreditCardBill::where('credit_account_id', $account->id)
            ->where('period_end', $preview['period_end']->format('Y-m-d'))
            ->exists();

        if ($existing) {
            return back()->with('error', 'Já existe uma fatura fechada para este período.');
        }

        $paymentAccount = Account::findOrFail($request->payment_account_id);
        abort_if($paymentAccount->user_id !== Auth::id(), 403);

        $dueDate = Carbon::parse($request->due_date);

        DB::transaction(function () use ($account, $paymentAccount, $preview, $total, $dueDate) {
            $bill = CreditCardBill::create([
                'user_id'            => Auth::id(),
                'credit_account_id'  => $account->id,
                'payment_account_id' => $paymentAccount->id,
                'period_start'       => $preview['period_start'],
                'period_end'         => $preview['period_end'],
                'due_date'           => $dueDate,
                'total_amount'       => $total,
                'status'             => 'closed',
            ]);

            $transaction = Auth::user()->transactions()->create([
                'type'        => 'expense',
                'account_id'  => $paymentAccount->id,
                'category_id' => null,
                'amount'      => $total,
                'description' => 'Fatura ' . $account->name . ' (' . $preview['period_end']->format('m/Y') . ')',
                'date'        => $dueDate->format('Y-m-d'),
                'notes'       => 'Gerado automaticamente ao fechar fatura.',
                'is_fixed'    => false,
            ]);

            $bill->update(['payment_transaction_id' => $transaction->id]);
        });

        return redirect()->route('credit-card-bills.index', $account)
            ->with('success', "Fatura fechada! Pagamento de R$ " . number_format($total, 2, ',', '.') . " agendado para " . $dueDate->format('d/m/Y') . ".");
    }

    public function pay(CreditCardBill $bill)
    {
        abort_if($bill->user_id !== Auth::id(), 403);
        $bill->update(['status' => 'paid']);
        return back()->with('success', 'Fatura marcada como paga!');
    }

    /**
     * Cards mensais: todos os ciclos de faturamento com seus totais.
     */
    public function months(Account $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        abort_if($account->type !== 'credit_card', 422);

        $closingDay = $account->closing_day ?? 21;
        $paymentDay = $account->payment_day ?? 10;
        $preview    = $this->buildCurrentPeriodPreview($account);

        $closedBills = CreditCardBill::where('credit_account_id', $account->id)
            ->orderByDesc('period_end')
            ->get();

        // ── Parcelas futuras agrupadas por ciclo de fatura ──────────────────
        $futureTransactions = Transaction::where('account_id', $account->id)
            ->where('type', 'expense')
            ->where('date', '>', $preview['period_end']->format('Y-m-d'))
            ->orderBy('date')
            ->with('category')
            ->get();

        $futureByPeriod = [];
        foreach ($futureTransactions as $t) {
            $txDate = $t->date;
            // Determina a qual ciclo de fechamento pertence
            if ($txDate->day <= $closingDay) {
                $periodEnd   = Carbon::create($txDate->year, $txDate->month, $closingDay);
            } else {
                $periodEnd   = Carbon::create($txDate->year, $txDate->month, $closingDay)->addMonth();
            }
            $periodStart = $periodEnd->copy()->subMonth()->addDay();
            $dueDate     = $periodEnd->copy()->addMonth()->startOfMonth()->addDays($paymentDay - 1);
            $key         = $periodEnd->format('Y-m');

            if (!isset($futureByPeriod[$key])) {
                $futureByPeriod[$key] = [
                    'label'        => $periodEnd->format('m/Y'),
                    'period_start' => $periodStart,
                    'period_end'   => $periodEnd,
                    'due_date'     => $dueDate,
                    'total'        => 0,
                    'status'       => 'reserved',
                    'bill'         => null,
                    'count'        => 0,
                ];
            }
            $futureByPeriod[$key]['total'] += (float) $t->amount;
            $futureByPeriod[$key]['count']++;
        }

        // ── Monta lista com todos os meses e ordena por period_end desc ────
        $months = collect();

        foreach ($futureByPeriod as $future) {
            $months->push($future);
        }

        $months->push([
            'label'        => $preview['period_end']->format('m/Y'),
            'period_start' => $preview['period_start'],
            'period_end'   => $preview['period_end'],
            'due_date'     => $preview['due_date'],
            'total'        => $preview['total_amount'],
            'status'       => 'open',
            'bill'         => null,
            'count'        => null,
        ]);

        foreach ($closedBills as $bill) {
            $months->push([
                'label'        => $bill->period_end->format('m/Y'),
                'period_start' => $bill->period_start,
                'period_end'   => $bill->period_end,
                'due_date'     => $bill->due_date,
                'total'        => (float) $bill->total_amount,
                'status'       => $bill->status,
                'bill'         => $bill,
                'count'        => null,
            ]);
        }

        $months = $months->sortBy(fn($m) => $m['period_end']->timestamp)->values();

        $totalAllTime = Transaction::where('account_id', $account->id)
            ->where('type', 'expense')
            ->sum('amount');

        return view('credit_card_bills.months', compact('account', 'months', 'totalAllTime', 'preview'));
    }

    /**
     * Extrato detalhado de um período específico do cartão.
     */
    public function statement(Request $request, Account $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        abort_if($account->type !== 'credit_card', 422);

        $closingDay = $account->closing_day ?? 21;

        // Período selecionado — padrão: fatura atual
        $preview = $this->buildCurrentPeriodPreview($account);

        if ($request->filled('period_start') && $request->filled('period_end')) {
            $periodStart = Carbon::parse($request->period_start);
            $periodEnd   = Carbon::parse($request->period_end);
        } else {
            $periodStart = $preview['period_start'];
            $periodEnd   = $preview['period_end'];
        }

        // Todas as transações do período
        $transactions = Transaction::where('account_id', $account->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
            ->with('category')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        // Agrupamento por categoria
        $byCategory = $transactions
            ->groupBy(fn($t) => $t->category_id ?? 0)
            ->map(fn($group) => [
                'name'  => $group->first()->category?->name ?? 'Sem categoria',
                'color' => $group->first()->category?->color ?? '#6B7280',
                'total' => $group->sum('amount'),
                'count' => $group->count(),
            ])
            ->sortByDesc('total')
            ->values();

        // Agrupamento por dia
        $byDay = $transactions->groupBy(fn($t) => $t->date->format('Y-m-d'));

        $total = $transactions->sum('amount');

        // Lista de períodos disponíveis (baseada em faturas fechadas + atual)
        $closedBills = CreditCardBill::where('credit_account_id', $account->id)
            ->orderByDesc('period_end')
            ->get();

        // Monta lista de períodos navegáveis (últimos 12 ciclos)
        $periods = collect();
        $ref = $preview['period_end']->copy();
        for ($i = 0; $i < 12; $i++) {
            $end   = $ref->copy()->subMonths($i);
            $start = $end->copy()->subMonth()->addDay()->startOfDay();
            // Ajusta ao dia de fechamento
            $end   = Carbon::create($end->year, $end->month, min($closingDay, $end->daysInMonth));
            $start = $end->copy()->subMonth()->addDay();
            $periods->push(['start' => $start, 'end' => $end]);
        }

        return view('credit_card_bills.statement', compact(
            'account', 'transactions', 'byCategory', 'byDay',
            'total', 'periodStart', 'periodEnd', 'periods', 'closedBills'
        ));
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function currentPeriod(Account $account): array
    {
        $closingDay = $account->closing_day ?? 21;
        $today      = now();

        if ($today->day <= $closingDay) {
            $periodEnd   = $today->copy()->startOfMonth()->addDays($closingDay - 1);
            $periodStart = $periodEnd->copy()->subMonth()->addDay();
        } else {
            $periodStart = $today->copy()->startOfMonth()->addDays($closingDay);
            $periodEnd   = $periodStart->copy()->addMonth()->subDay();
        }

        return ['start' => $periodStart, 'end' => $periodEnd];
    }

    private function buildCurrentPeriodPreview(Account $account): array
    {
        $period     = $this->currentPeriod($account);
        $total      = (float) Transaction::where('account_id', $account->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$period['start']->format('Y-m-d'), $period['end']->format('Y-m-d')])
            ->sum('amount');

        $paymentDay = $account->payment_day ?? 10;
        $dueDate    = $period['end']->copy()->addMonth()->startOfMonth()->addDays($paymentDay - 1);

        return [
            'period_start' => $period['start'],
            'period_end'   => $period['end'],
            'due_date'     => $dueDate,
            'total_amount' => $total,
        ];
    }
}
