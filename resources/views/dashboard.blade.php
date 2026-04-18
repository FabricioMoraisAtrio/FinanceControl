<x-app-layout>
    <x-slot name="title">Dashboard — FinanceControl</x-slot>

    @php
        $monthNames = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    @endphp

    <div class="mb-7">
        <h1 class="text-2xl font-bold text-white">Dashboard</h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ $monthNames[$month] }} de {{ $year }}</p>
    </div>

    {{-- ── LINHA 1: CARDS GRANDES ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-4">

        {{-- Saldo Real --}}
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-2xl p-7">
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Saldo Real</p>
                <div class="w-10 h-10 bg-emerald-500/15 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
            <p class="text-4xl font-bold text-white tracking-tight">
                R$ {{ number_format($totalBalance, 2, ',', '.') }}
            </p>
            <p class="text-xs text-slate-500 mt-2">contas corrente, poupança e dinheiro</p>

            {{-- Contas rápidas --}}
            @if($accounts->whereNotIn('type', ['credit_card'])->count())
            <div class="mt-5 space-y-2 border-t border-slate-700 pt-4">
                @foreach($accounts->whereNotIn('type', ['credit_card']) as $acc)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full" style="background-color:{{ $acc->color }}"></div>
                        <span class="text-xs text-slate-400">{{ $acc->name }}</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-200">R$ {{ number_format($acc->balance, 2, ',', '.') }}</span>
                </div>
                @endforeach

                {{-- Deduções de crédito --}}
                @if($openCreditTotal > 0 || $futureBillPayments > 0)
                <div class="border-t border-slate-700/50 pt-2 mt-1 space-y-1">
                    @if($openCreditTotal > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-orange-400/80">Fatura(s) em aberto</span>
                        <span class="text-xs font-semibold text-orange-400">-R$ {{ number_format($openCreditTotal, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($futureBillPayments > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-red-400/80">Faturas a pagar</span>
                        <span class="text-xs font-semibold text-red-400">-R$ {{ number_format($futureBillPayments, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex items-center justify-between border-t border-slate-700/50 pt-1 mt-1">
                        <span class="text-xs text-slate-400 font-semibold">Saldo disponível</span>
                        <span class="text-xs font-bold {{ $projectedBalance >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            R$ {{ number_format($projectedBalance, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Saídas Previstas --}}
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-red-500/20 rounded-2xl p-7">
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Saídas Previstas</p>
                <div class="w-10 h-10 bg-red-500/15 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l-4-4m0 0l4-4m-4 4h11a4 4 0 010 8h-1"/>
                    </svg>
                </div>
            </div>
            <p class="text-4xl font-bold text-red-400 tracking-tight">
                R$ {{ number_format($saídasPrevistas, 2, ',', '.') }}
            </p>
            <p class="text-xs text-slate-500 mt-2">despesas fixas + parcelas do mês</p>

            <div class="mt-5 space-y-2 border-t border-slate-700 pt-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-purple-400 inline-block"></span> Fixas
                    </span>
                    <span class="text-xs font-semibold text-slate-200">R$ {{ number_format($fixedExpenses, 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span> Parcelas
                    </span>
                    <span class="text-xs font-semibold text-slate-200">R$ {{ number_format($installmentExpenses, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── LINHA 2: CARDS MENORES ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-7">
        <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-4">
            <p class="text-xs text-slate-500 mb-1 uppercase tracking-widest">Entradas</p>
            <p class="text-lg font-bold text-emerald-400">R$ {{ number_format($monthlyIncome, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-4">
            <p class="text-xs text-slate-500 mb-1 uppercase tracking-widest">Total Saídas</p>
            <p class="text-lg font-bold text-red-400">R$ {{ number_format($monthlyExpense, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-900 border border-blue-500/20 rounded-xl px-4 py-4">
            <p class="text-xs text-slate-500 mb-1 uppercase tracking-widest flex items-center gap-1">
                <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Crédito
            </p>
            <p class="text-lg font-bold text-blue-400">R$ {{ number_format($creditCardExpense, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-4">
            <p class="text-xs text-slate-500 mb-1 uppercase tracking-widest">Débito/Outros</p>
            <p class="text-lg font-bold text-slate-200">R$ {{ number_format($otherExpense, 2, ',', '.') }}</p>
        </div>
    </div>

    {{-- ── LINHA 3: CONTEÚDO ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Lançamentos Recentes --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-white">Lançamentos Recentes</h2>
                    <a href="{{ route('transactions.create') }}"
                        class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 px-3 py-1.5 rounded-lg transition-colors">
                        + Novo
                    </a>
                </div>
                @if($recentTransactions->isEmpty())
                    <p class="text-slate-600 text-sm text-center py-8">Nenhum lançamento ainda.</p>
                @else
                <div class="space-y-1">
                    @foreach($recentTransactions as $t)
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800/60 transition-colors">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                            {{ $t->type === 'income' ? 'bg-emerald-500/15' : ($t->type === 'expense' ? 'bg-red-500/15' : 'bg-blue-500/15') }}">
                            @if($t->type === 'income')
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            @elseif($t->type === 'expense')
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            @else
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-200 truncate flex items-center gap-2">
                                {{ preg_replace('/\s*\(\d+\/\d+\)\s*$/', '', $t->description) }}
                                @if($t->installment_total)
                                    <span class="text-xs bg-orange-500/15 text-orange-400 px-1.5 py-0.5 rounded font-semibold shrink-0">
                                        {{ $t->installment_total }}x
                                    </span>
                                @endif
                            </p>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($t->account->type === 'credit_card')
                                    <span class="text-xs text-blue-400 bg-blue-500/10 px-1.5 py-0.5 rounded font-medium">Crédito</span>
                                @endif
                                <p class="text-xs text-slate-500">{{ $t->category?->name ?? '—' }} · {{ $t->account->name }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-bold {{ $t->type === 'income' ? 'text-emerald-400' : ($t->type === 'expense' ? 'text-red-400' : 'text-blue-400') }}">
                                {{ $t->type === 'expense' ? '-' : '+' }}R$ {{ number_format($t->amount, 2, ',', '.') }}
                            </p>
                            <p class="text-xs text-slate-600">{{ $t->date->format('d/m') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <a href="{{ route('transactions.index') }}"
                    class="block text-center text-xs font-semibold text-slate-500 hover:text-emerald-400 mt-4 pt-3 border-t border-slate-800 transition-colors">
                    Ver todos os lançamentos →
                </a>
                @endif
            </div>

            {{-- Próximas Parcelas --}}
            @if($upcomingInstallments->count())
            <div class="bg-slate-900 border border-orange-500/20 rounded-2xl p-5">
                <h2 class="font-semibold text-white mb-1 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                    Próximas Parcelas
                </h2>
                <p class="text-xs text-slate-500 mb-4">parcelas pendentes a partir de hoje</p>
                <div class="space-y-2">
                    @foreach($upcomingInstallments as $t)
                    <div class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-800/40 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-xs font-bold text-orange-400 bg-orange-500/10 px-2 py-1 rounded-lg shrink-0">
                                {{ $t->installmentLabel() }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm text-slate-200 truncate">{{ Str::before($t->description, ' (') }}</p>
                                <p class="text-xs text-slate-500">{{ $t->date->format('M/Y') }} · {{ $t->account->name }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-red-400 shrink-0 ml-3">
                            -R$ {{ number_format($t->amount, 2, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Lateral --}}
        <div class="space-y-5">

            {{-- Cartão de Crédito --}}
            @if($creditAccounts->count())
            <div class="bg-slate-900 border border-blue-500/20 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <h2 class="font-semibold text-white">Cartão de Crédito</h2>
                </div>
                @foreach($creditAccounts as $acc)
                <div class="bg-slate-800 rounded-xl p-4 mb-2">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full" style="background-color:{{ $acc->color }}"></div>
                            <span class="text-sm font-semibold text-slate-200">{{ $acc->name }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mb-1">Fatura do mês</p>
                    <p class="text-xl font-bold text-blue-400">
                        R$ {{ number_format($creditCardExpense, 2, ',', '.') }}
                    </p>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Orçamentos --}}
            @if($budgets->isNotEmpty())
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-white">Orçamentos</h2>
                    <a href="{{ route('budgets.index') }}" class="text-xs text-slate-500 hover:text-slate-300">Ver todos</a>
                </div>
                <div class="space-y-4">
                    @foreach($budgets->take(5) as $budget)
                    @php $pct = $budget->getPercentage(); @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="font-medium text-slate-300">{{ $budget->category->name }}</span>
                            <span class="{{ $pct >= 100 ? 'text-red-400 font-bold' : 'text-slate-500' }}">{{ number_format($pct, 0) }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-yellow-400' : 'bg-emerald-500') }}"
                                style="width: {{ min(100, $pct) }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
