<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OpenBillsController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $user       = Auth::user();
        $today      = now()->toDateString();
        $filterType = $request->get('payment_type'); // 'credit', 'debit', ou null

        // Exibe parcelas a partir do início do mês atual para não sumir
        // quando a data da parcela vira ontem (sem marcação de "pago")
        $fromDate = now()->startOfMonth()->toDateString();

        // ── Parcelamentos em aberto ──────────────────────────────────────────
        $query = $user->transactions()
            ->expense()
            ->whereNotNull('installment_group_id')
            ->where('date', '>=', $fromDate)
            ->with(['account', 'category']);

        if ($filterType === 'credit') {
            $query->whereHas('account', fn($q) => $q->where('type', 'credit_card'));
        } elseif ($filterType === 'debit') {
            $query->whereHas('account', fn($q) => $q->whereNotIn('type', ['credit_card']));
        }

        $futureInstallments = $query->orderBy('date')->get();

        // Agrupa por installment_group_id e monta os dados de cada dívida
        $openBills = $futureInstallments
            ->groupBy('installment_group_id')
            ->map(function ($group) use ($today) {
                $first = $group->sortBy('installment_current')->first();
                $last  = $group->sortBy('installment_current')->last();

                // Remove o sufixo " (x/N)" da descrição
                $name = preg_replace('/\s*\(\d+\/\d+\)\s*$/', '', $first->description);

                $account    = $first->account;
                $txDate     = $first->date; // Carbon

                // Para cartão de crédito, calcula a data de vencimento da fatura
                // Ex: compra em 16/04, fecha 21, vence 10 → vence 10/05
                if ($account && $account->type === 'credit_card') {
                    $closingDay = $account->closing_day ?? 21;
                    $paymentDay = $account->payment_day ?? 10;

                    if ($txDate->day <= $closingDay) {
                        // Cai na fatura que fecha neste mês → vence no mês seguinte
                        $dueDate = \Carbon\Carbon::create($txDate->year, $txDate->month, $paymentDay)->addMonth();
                    } else {
                        // Passou do fechamento → fatura do próximo ciclo → vence em 2 meses
                        $dueDate = \Carbon\Carbon::create($txDate->year, $txDate->month, $paymentDay)->addMonths(2);
                    }
                    $nextDate = $dueDate;
                    $overdue  = false; // vencimento futuro, nunca "vencida" no cartão
                } else {
                    $nextDate = $txDate;
                    $overdue  = $txDate->format('Y-m-d') < $today;
                }

                return [
                    'name'             => $name,
                    'total_remaining'  => (float) $group->sum('amount'),
                    'remaining_count'  => $group->count(),
                    'installment_total'=> $first->installment_total,
                    'amount_per'       => (float) $first->amount,
                    'end_date'         => $last->date,
                    'next_date'        => $nextDate,
                    'overdue'          => $overdue,
                    'account'          => $account,
                    'category'         => $first->category,
                    'group_id'         => $first->installment_group_id,
                    'payer'            => $first->notes ? $this->extractPayer($first->notes) : null,
                ];
            })
            ->sortBy([
                // 1º: minhas (sem payer) antes das de terceiros
                fn ($a, $b) => ($a['payer'] === null ? 0 : 1) <=> ($b['payer'] === null ? 0 : 1),
                // 2º: dentro de cada grupo, ordena pelo nome do payer (agrupa Pai, André, etc.)
                fn ($a, $b) => ($a['payer'] ?? '') <=> ($b['payer'] ?? ''),
                // 3º: dentro do mesmo payer, ordena por data de término
                fn ($a, $b) => $a['end_date'] <=> $b['end_date'],
            ])
            ->values();

        // ── Resumo de entradas (salário) do mês atual ────────────────────────
        $month = now()->month;
        $year  = now()->year;

        $salaryCategory = $user->categories()
            ->whereRaw('LOWER(name) = ?', ['salário'])
            ->first();

        $grossSalary = $salaryCategory
            ? $user->transactions()->income()->ofMonth($month, $year)
                ->where('category_id', $salaryCategory->id)->sum('amount')
            : 0;

        // Totais consolidados
        $totalRemaining    = $openBills->sum('total_remaining');
        $totalMonthly      = $openBills->sum('amount_per');

        return view('open_bills.index', compact(
            'openBills', 'totalRemaining', 'totalMonthly', 'grossSalary', 'filterType'
        ));
    }

    private function extractPayer(string $notes): ?string
    {
        // Convenção: notas que começam com "[@Nome]" identificam o responsável
        if (preg_match('/^\[@(.+?)\]/', $notes, $m)) {
            return $m[1];
        }
        return null;
    }
}
