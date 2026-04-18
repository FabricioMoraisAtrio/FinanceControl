<x-app-layout>
    <x-slot name="title">Fatura — {{ $account->name }}</x-slot>

    <div class="mb-6">
        <a href="{{ route('accounts.index') }}" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">← Contas</a>
        <div class="flex items-center justify-between mt-1">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color:{{ $account->color }}20">
                    <div class="w-3 h-3 rounded-full" style="background-color:{{ $account->color }}"></div>
                </div>
                <h1 class="text-2xl font-bold text-white">Fatura — {{ $account->name }}</h1>
            </div>
            <a href="{{ route('credit-card-bills.statement', $account) }}"
                class="inline-flex items-center gap-2 border border-blue-500/30 hover:bg-blue-500/10 text-blue-400 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ver Extrato
            </a>
        </div>
    </div>

    {{-- Fatura Atual (Preview) --}}
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-blue-500/30 rounded-2xl p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Fatura Atual em Aberto</p>
                <p class="text-xs text-slate-500">
                    {{ $preview['period_start']->format('d/m/Y') }} até {{ $preview['period_end']->format('d/m/Y') }}
                    · Vence {{ $preview['due_date']->format('d/m/Y') }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-blue-400">R$ {{ number_format($preview['total_amount'], 2, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $periodTransactions->count() }} lançamento(s)</p>
            </div>
        </div>

        {{-- Lista de gastos do período --}}
        @if($periodTransactions->isNotEmpty())
        <div class="border-t border-slate-700 pt-4 mb-4 max-h-48 overflow-y-auto space-y-1">
            @foreach($periodTransactions as $t)
            <div class="flex items-center justify-between text-sm py-1">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-xs text-slate-600 shrink-0">{{ $t->date->format('d/m') }}</span>
                    <span class="text-slate-300 truncate">{{ $t->description }}</span>
                    @if($t->category)
                        <span class="text-xs text-slate-600 shrink-0">· {{ $t->category->name }}</span>
                    @endif
                </div>
                <span class="text-red-400 font-semibold shrink-0 ml-3">-R$ {{ number_format($t->amount, 2, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Formulário de fechamento --}}
        @if($preview['total_amount'] > 0)
        <form action="{{ route('credit-card-bills.close', $account) }}" method="POST"
            class="border-t border-slate-700 pt-4 flex flex-wrap gap-3 items-end"
            onsubmit="return confirm('Fechar fatura de R$ {{ number_format($preview['total_amount'], 2, ',', '.') }}? Será agendado um pagamento na conta selecionada.')">
            @csrf

            <div>
                <label class="block text-xs text-slate-500 mb-1">Debitar na conta</label>
                <select name="payment_account_id" required
                    class="bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione...</option>
                    @foreach($checkingAccounts as $acc)
                        <option value="{{ $acc->id }}"
                            {{ $account->payment_account_id == $acc->id ? 'selected' : '' }}>
                            {{ $acc->name }} — R$ {{ number_format($acc->balance, 2, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-500 mb-1">Data de vencimento</label>
                <input type="date" name="due_date" value="{{ $preview['due_date']->format('Y-m-d') }}" required
                    class="bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold px-5 py-2 rounded-lg transition-colors">
                Fechar Fatura
            </button>
        </form>
        @else
            <p class="text-slate-600 text-sm border-t border-slate-700 pt-4">Nenhum gasto neste período.</p>
        @endif
    </div>

    {{-- Histórico de Faturas --}}
    @if($bills->isNotEmpty())
    <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-widest mb-3">Histórico de Faturas</h2>
    <div class="space-y-3">
        @foreach($bills as $bill)
        <div class="bg-slate-900 border {{ $bill->isPaid() ? 'border-slate-800' : 'border-orange-500/30' }} rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        @if($bill->isPaid())
                            <span class="text-xs bg-emerald-500/15 text-emerald-400 px-2 py-0.5 rounded-full font-semibold">✓ Paga</span>
                        @else
                            <span class="text-xs bg-orange-500/15 text-orange-400 px-2 py-0.5 rounded-full font-semibold">Pendente</span>
                        @endif
                        <span class="text-xs text-slate-500">
                            {{ $bill->period_start->format('d/m') }} – {{ $bill->period_end->format('d/m/Y') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">
                        Vencimento: {{ $bill->due_date->format('d/m/Y') }}
                        @if($bill->paymentAccount) · {{ $bill->paymentAccount->name }} @endif
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <p class="text-xl font-bold {{ $bill->isPaid() ? 'text-slate-400' : 'text-orange-400' }}">
                        R$ {{ number_format($bill->total_amount, 2, ',', '.') }}
                    </p>
                    @if(!$bill->isPaid())
                    <form action="{{ route('credit-card-bills.pay', $bill) }}" method="POST"
                        onsubmit="return confirm('Confirmar pagamento desta fatura?')">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="text-sm bg-emerald-500 hover:bg-emerald-400 text-white font-semibold px-4 py-1.5 rounded-lg transition-colors">
                            Marcar Paga
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Configurações do cartão --}}
    <div class="mt-6 bg-slate-900 border border-slate-800 rounded-2xl p-5">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Configurações do Cartão</h3>
        <form action="{{ route('accounts.update', $account) }}" method="POST" class="flex flex-wrap gap-4 items-end">
            @csrf @method('PUT')
            <input type="hidden" name="name"  value="{{ $account->name }}">
            <input type="hidden" name="type"  value="{{ $account->type }}">
            <input type="hidden" name="color" value="{{ $account->color }}">
            <input type="hidden" name="icon"  value="{{ $account->icon }}">
            <input type="hidden" name="initial_balance" value="{{ $account->initial_balance }}">
            <input type="hidden" name="active" value="{{ $account->active ? '1' : '0' }}">

            <div>
                <label class="block text-xs text-slate-500 mb-1">Dia de fechamento</label>
                <input type="number" name="closing_day" value="{{ $account->closing_day }}" min="1" max="31"
                    class="w-24 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Dia de vencimento</label>
                <input type="number" name="payment_day" value="{{ $account->payment_day }}" min="1" max="31"
                    class="w-24 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <button type="submit" class="text-sm text-slate-400 border border-slate-700 px-4 py-2 rounded-lg hover:bg-slate-800 transition-colors">
                Salvar configurações
            </button>
        </form>
    </div>
</x-app-layout>
