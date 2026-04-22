<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->transactions()->with(['account', 'category', 'accountTo']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_fixed')) {
            $query->where('is_fixed', $request->is_fixed === '1');
        }

        if ($request->filled('account_type')) {
            if ($request->account_type === 'credit') {
                $query->credit();
            } elseif ($request->account_type === 'debit') {
                $query->debit();
            }
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $query->whereMonth('date', $month)->whereYear('date', $year);

        $transactions = $query->orderByDesc('date')->orderByDesc('id')->paginate(200)->withQueryString();

        $accounts   = $user->accounts()->where('active', true)->orderBy('name')->get();
        $categories = $user->categories()->orderBy('name')->get();

        $totals = $user->transactions()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->selectRaw("type, SUM(amount) as total")
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('transactions.index', compact('transactions', 'accounts', 'categories', 'month', 'year', 'totals'));
    }

    public function create()
    {
        $accounts   = Auth::user()->accounts()->where('active', true)->orderBy('name')->get();
        $categories = Auth::user()->categories()->orderBy('type')->orderBy('name')->get();
        return view('transactions.create', compact('accounts', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'               => 'required|in:income,expense,transfer',
            'account_id'         => 'required|exists:accounts,id',
            'account_to_id'      => 'nullable|required_if:type,transfer|exists:accounts,id',
            'category_id'        => 'nullable|exists:categories,id',
            'amount'             => 'required|numeric|min:0.01',
            'description'        => 'required|string|max:255',
            'date'               => 'required|date',
            'notes'              => 'nullable|string',
            'is_fixed'           => 'boolean',
            'installment_total'  => 'nullable|integer|min:2|max:60',
        ]);

        $isFixed            = $request->boolean('is_fixed');
        // Parcelamento só se aplica a despesas com total explicitamente > 1
        $rawTotal           = (int) ($validated['installment_total'] ?? 1);
        $installmentTotal   = ($validated['type'] === 'expense' && $rawTotal > 1) ? $rawTotal : 1;
        $isInstallment      = $installmentTotal > 1;

        DB::transaction(function () use ($validated, $isFixed, $installmentTotal, $isInstallment) {
            $groupId  = $isInstallment ? (string) Str::uuid() : null;
            $baseDate = \Carbon\Carbon::parse($validated['date']);
            $amountPerInstallment = round($validated['amount'] / $installmentTotal, 2);

            for ($i = 1; $i <= $installmentTotal; $i++) {
                $installDate = $isInstallment ? $baseDate->copy()->addMonths($i - 1) : $baseDate;
                $descSuffix  = $isInstallment ? " ({$i}/{$installmentTotal})" : '';

                Auth::user()->transactions()->create([
                    'type'                => $validated['type'],
                    'account_id'          => $validated['account_id'],
                    'account_to_id'       => $validated['account_to_id'] ?? null,
                    'category_id'         => $validated['category_id'] ?? null,
                    'amount'              => $amountPerInstallment,
                    'description'         => $validated['description'] . $descSuffix,
                    'date'                => $installDate->format('Y-m-d'),
                    'notes'               => $validated['notes'] ?? null,
                    'is_fixed'            => $isFixed,
                    'installment_group_id'=> $groupId,
                    'installment_current' => $isInstallment ? $i : null,
                    'installment_total'   => $isInstallment ? $installmentTotal : null,
                ]);
            }
            // Saldo calculado dinamicamente pelo model — não é necessário atualizar manualmente
        });

        $msg = $isInstallment
            ? "{$installmentTotal} parcelas criadas com sucesso!"
            : 'Lançamento registrado com sucesso!';

        // ── Dízimo automático ────────────────────────────────────────────────
        // Se a categoria do lançamento for "Salário" (income), cria dízimo (10%)
        // para o próximo domingo
        $titheSuggestion = null;
        if ($validated['type'] === 'income' && isset($validated['category_id'])) {
            $cat = \App\Models\Category::find($validated['category_id']);
            if ($cat && mb_strtolower($cat->name) === 'salário') {
                $titheSuggestion = $this->createTithe($validated);
                if ($titheSuggestion) {
                    $msg .= " Dízimo de R$ " . number_format($titheSuggestion['amount'], 2, ',', '.') . " agendado para " . $titheSuggestion['date'] . " (próximo domingo).";
                }
            }
        }

        return redirect()->route('transactions.index')->with('success', $msg);
    }

    public function show(Transaction $transaction)
    {
        abort_if($transaction->user_id !== Auth::id(), 403);
        $installments = [];
        if ($transaction->installment_group_id) {
            $installments = Transaction::where('installment_group_id', $transaction->installment_group_id)
                ->orderBy('installment_current')
                ->get();
        }
        return view('transactions.show', compact('transaction', 'installments'));
    }

    public function edit(Transaction $transaction)
    {
        abort_if($transaction->user_id !== Auth::id(), 403);
        $accounts   = Auth::user()->accounts()->where('active', true)->orderBy('name')->get();
        $categories = Auth::user()->categories()->orderBy('type')->orderBy('name')->get();
        return view('transactions.edit', compact('transaction', 'accounts', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        abort_if($transaction->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'date'        => 'required|date',
            'notes'       => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'is_fixed'    => 'boolean',
        ]);

        $validated['is_fixed'] = $request->boolean('is_fixed');
        $transaction->update($validated);

        return redirect()->route('transactions.index', ['month' => $transaction->date->month, 'year' => $transaction->date->year])
            ->with('success', 'Lançamento atualizado com sucesso!');
    }

    public function destroy(Transaction $transaction)
    {
        abort_if($transaction->user_id !== Auth::id(), 403);

        // Saldo é calculado dinamicamente — basta deletar o registro
        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Lançamento removido com sucesso!');
    }

    // Cancela todas as parcelas restantes do grupo
    public function destroyInstallmentGroup(Transaction $transaction)
    {
        abort_if($transaction->user_id !== Auth::id(), 403);
        if (!$transaction->installment_group_id) {
            return $this->destroy($transaction);
        }

        $pending = Transaction::where('installment_group_id', $transaction->installment_group_id)
            ->where('installment_current', '>=', $transaction->installment_current)
            ->get();

        // Saldo é calculado dinamicamente — basta deletar os registros
        Transaction::whereIn('id', $pending->pluck('id'))->delete();

        return redirect()->route('transactions.index')->with('success', 'Parcelas restantes canceladas!');
    }

    // ── Dízimo ───────────────────────────────────────────────────────────────

    private function createTithe(array $validated): ?array
    {
        $user = Auth::user();

        // Busca ou cria a categoria "Dízimo"
        $titheCategory = $user->categories()
            ->where('name', 'Dízimo')
            ->first();

        if (!$titheCategory) {
            $titheCategory = $user->categories()->create([
                'name'  => 'Dízimo',
                'type'  => 'expense',
                'color' => '#8B5CF6',
                'icon'  => 'tithe',
            ]);
        }

        // Próximo domingo a partir da data do lançamento
        $baseDate   = \Carbon\Carbon::parse($validated['date']);
        $nextSunday = $baseDate->copy()->next(\Carbon\Carbon::SUNDAY);

        $titheAmount = round($validated['amount'] * 0.10, 2);

        // Calcula o total de salários para este lançamento (pode ser parcial se parcelado)
        // Usa o amount bruto do request para o dízimo
        $user->transactions()->create([
            'type'        => 'expense',
            'account_id'  => $validated['account_id'],
            'category_id' => $titheCategory->id,
            'amount'      => $titheAmount,
            'description' => 'Dízimo',
            'date'        => $nextSunday->format('Y-m-d'),
            'notes'       => 'Gerado automaticamente (10% do salário de ' . \Carbon\Carbon::parse($validated['date'])->format('d/m/Y') . ')',
            'is_fixed'    => false,
        ]);

        return [
            'amount' => $titheAmount,
            'date'   => $nextSunday->format('d/m/Y'),
        ];
    }
}
