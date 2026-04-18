<x-app-layout>
    <x-slot name="title">Contas — FinanceControl</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Contas</h1>
        <a href="{{ route('accounts.create') }}"
            class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-emerald-900/30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nova Conta
        </a>
    </div>

    @if($accounts->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center">
            <p class="text-slate-500">Nenhuma conta cadastrada.
                <a href="{{ route('accounts.create') }}" class="text-emerald-400 hover:text-emerald-300">Criar agora</a>.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($accounts as $account)
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 hover:border-slate-700 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $account->color }}20">
                        <div class="w-4 h-4 rounded-full" style="background-color: {{ $account->color }}"></div>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-200">{{ $account->name }}</p>
                        <p class="text-xs text-slate-500">{{ $account->getTypeLabel() }}</p>
                    </div>
                </div>

                @if($account->type === 'credit_card')
                    @php
                        $spent = $account->total_spent;
                        $limit = (float) ($account->credit_limit ?? 0);
                        $pct   = $limit > 0 ? min(100, round($spent / $limit * 100)) : null;
                        $barColor = $pct === null ? 'bg-blue-500'
                                  : ($pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-blue-500'));
                    @endphp
                    <p class="text-2xl font-bold text-white mb-1">R$ {{ number_format($spent, 2, ',', '.') }}</p>
                    @if($limit > 0)
                        <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                            <span>{{ $pct }}% do limite</span>
                            <span>Limite: R$ {{ number_format($limit, 2, ',', '.') }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-700 rounded-full mb-4">
                            <div class="h-1.5 rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                        </div>
                    @else
                        <p class="text-xs text-slate-600 mb-4">Total gasto no cartão</p>
                    @endif
                @else
                    <p class="text-2xl font-bold text-white mb-5">R$ {{ number_format($account->balance, 2, ',', '.') }}</p>
                @endif

                <div class="flex items-center gap-2">
                    @if($account->type === 'credit_card')
                        <a href="{{ route('credit-card-bills.index', $account) }}"
                            class="flex-1 text-center text-sm text-blue-400 hover:text-blue-300 border border-blue-500/30 rounded-lg py-1.5 hover:bg-blue-500/10 transition-colors">
                            💳 Fatura
                        </a>
                    @endif
                    <a href="{{ route('accounts.edit', $account) }}"
                        class="flex-1 text-center text-sm text-slate-400 hover:text-slate-200 border border-slate-700 rounded-lg py-1.5 hover:bg-slate-800 transition-colors">
                        Editar
                    </a>
                    <form action="{{ route('accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Remover esta conta?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="text-sm text-red-400 hover:text-red-300 border border-red-500/20 rounded-lg py-1.5 px-3 hover:bg-red-500/10 transition-colors">
                            Remover
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
