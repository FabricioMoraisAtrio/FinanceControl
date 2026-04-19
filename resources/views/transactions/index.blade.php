<x-app-layout>
    <x-slot name="title">Lançamentos — FinanceControl</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Lançamentos</h1>
        <a href="{{ route('transactions.create') }}"
            class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-emerald-900/30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Novo Lançamento
        </a>
    </div>

    {{-- Filtros --}}
    <div x-data="{ filtersOpen: false }" class="mb-5">

    <button type="button" @click="filtersOpen = !filtersOpen"
        class="md:hidden w-full flex items-center justify-between bg-slate-900 border border-slate-800 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 mb-2">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <span>Filtros</span>
            @if(request()->hasAny(['type','is_fixed','account_id','account_type','category_id']))
                <span class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full font-semibold">Ativo</span>
            @endif
        </div>
        <svg :class="filtersOpen ? 'rotate-180' : ''" class="w-4 h-4 text-slate-500 transition-transform duration-200"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div :class="filtersOpen ? 'block' : 'hidden md:block'">
    <form method="GET" id="filter-form" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-slate-500 mb-1 font-medium">Tipo</label>
            <select name="type" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">Todos</option>
                <option value="income"   {{ request('type') === 'income'   ? 'selected' : '' }}>Entradas</option>
                <option value="expense"  {{ request('type') === 'expense'  ? 'selected' : '' }}>Saídas</option>
                <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Transferências</option>
            </select>
        </div>

        {{-- Débito / Crédito --}}
        <div>
            <label class="block text-xs text-slate-500 mb-1 font-medium">Pagamento</label>
            <select name="account_type" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">Todos</option>
                <option value="debit"  {{ request('account_type') === 'debit'  ? 'selected' : '' }}>Débito</option>
                <option value="credit" {{ request('account_type') === 'credit' ? 'selected' : '' }}>Crédito</option>
            </select>
        </div>

        <div>
            <label class="block text-xs text-slate-500 mb-1 font-medium">Recorrência</label>
            <select name="is_fixed" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">Todas</option>
                <option value="1" {{ request('is_fixed') === '1' ? 'selected' : '' }}>Fixas</option>
                <option value="0" {{ request('is_fixed') === '0' ? 'selected' : '' }}>Variáveis</option>
            </select>
        </div>

        <div>
            <label class="block text-xs text-slate-500 mb-1 font-medium">Conta</label>
            <select name="account_id" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">Todas</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-slate-500 mb-1 font-medium">Categoria</label>
            <select name="category_id" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">Todas</option>
                <optgroup label="Despesas">
                    @foreach($categories->where('type', 'expense') as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </optgroup>
                <optgroup label="Receitas">
                    @foreach($categories->where('type', 'income') as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </optgroup>
            </select>
        </div>

        <div>
            <label class="block text-xs text-slate-500 mb-1 font-medium">Mês</label>
            <select name="month" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('M') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-slate-500 mb-1 font-medium">Ano</label>
            <select name="year" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @foreach(range(now()->year - 2, now()->year + 1) as $y)
                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        @if(request()->hasAny(['type', 'is_fixed', 'account_id', 'account_type', 'category_id']))
            <a href="{{ route('transactions.index') }}" class="text-sm text-slate-500 hover:text-slate-300 py-2">Limpar filtros</a>
        @endif
    </form>
    </div>
    </div>

    {{-- Totais do mês --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-0.5">Entradas</p>
            <p class="text-base font-bold text-emerald-400">+R$ {{ number_format($totals['income'] ?? 0, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-0.5">Saídas</p>
            <p class="text-base font-bold text-red-400">-R$ {{ number_format($totals['expense'] ?? 0, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-0.5">Saldo</p>
            @php $saldo = ($totals['income'] ?? 0) - ($totals['expense'] ?? 0); @endphp
            <p class="text-base font-bold {{ $saldo >= 0 ? 'text-white' : 'text-red-400' }}">
                {{ $saldo >= 0 ? '+' : '' }}R$ {{ number_format($saldo, 2, ',', '.') }}
            </p>
        </div>
    </div>

    @if($transactions->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center">
            <p class="text-slate-500 text-sm">Nenhum lançamento encontrado.</p>
            <a href="{{ route('transactions.create') }}" class="inline-block mt-3 text-sm text-emerald-400 hover:text-emerald-300">
                Adicionar primeiro lançamento →
            </a>
        </div>
    @else
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-800">
                    <tr class="text-xs text-slate-500 font-semibold uppercase tracking-wide">
                        <th class="text-left px-5 py-3.5">Data</th>
                        <th class="text-left px-5 py-3.5">Descrição</th>
                        <th class="text-left px-5 py-3.5 hidden sm:table-cell">Categoria</th>
                        <th class="text-left px-5 py-3.5 hidden md:table-cell">Conta</th>
                        <th class="text-left px-5 py-3.5 hidden lg:table-cell">Tipo</th>
                        <th class="text-right px-5 py-3.5">Valor</th>
                        <th class="px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($transactions as $t)
                    <tr class="hover:bg-slate-800/40 transition-colors group">
                        <td class="px-5 py-3.5 text-slate-500 text-xs whitespace-nowrap">{{ $t->date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-medium text-slate-200">{{ $t->description }}</span>

                                {{-- Badge: Parcelamento --}}
                                @if($t->isInstallment())
                                    <span class="text-xs bg-orange-500/15 text-orange-400 px-1.5 py-0.5 rounded-md font-semibold">
                                        {{ $t->installmentLabel() }}
                                    </span>
                                @endif

                                {{-- Badge: Fixa --}}
                                @if($t->is_fixed)
                                    <span class="text-xs bg-purple-500/15 text-purple-400 px-1.5 py-0.5 rounded-md font-medium">Fixa</span>
                                @endif

                                {{-- Badge: Crédito --}}
                                @if($t->account->type === 'credit_card')
                                    <span class="text-xs bg-blue-500/10 text-blue-400 px-1.5 py-0.5 rounded-md font-medium">💳</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs hidden sm:table-cell">{{ $t->category?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs hidden md:table-cell">{{ $t->account->name }}</td>
                        <td class="px-5 py-3.5 hidden lg:table-cell">
                            @if($t->type === 'income')
                                <span class="inline-flex items-center gap-1 text-xs text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full font-medium">↑ Entrada</span>
                            @elseif($t->type === 'expense')
                                <span class="inline-flex items-center gap-1 text-xs text-red-400 bg-red-500/10 px-2 py-0.5 rounded-full font-medium">↓ Saída</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full font-medium">⇄ Transf.</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right font-bold whitespace-nowrap
                            {{ $t->type === 'income' ? 'text-emerald-400' : ($t->type === 'expense' ? 'text-red-400' : 'text-blue-400') }}">
                            {{ $t->type === 'expense' ? '-' : '+' }}R$ {{ number_format($t->amount, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('transactions.edit', $t) }}"
                                    class="p-1.5 text-slate-600 hover:text-slate-300 rounded-lg hover:bg-slate-700 transition-colors"
                                    title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>

                                {{-- Cancelar parcelas restantes --}}
                                @if($t->isInstallment())
                                    <form action="{{ route('transactions.installments.destroy', $t) }}" method="POST"
                                        onsubmit="return confirm('Cancelar esta e todas as parcelas seguintes ({{ $t->installmentLabel() }})? O saldo será revertido.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 text-slate-600 hover:text-orange-400 rounded-lg hover:bg-orange-500/10 transition-colors"
                                            title="Cancelar parcelas restantes">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('transactions.destroy', $t) }}" method="POST" onsubmit="return confirm('Remover este lançamento?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 text-slate-600 hover:text-red-400 rounded-lg hover:bg-red-500/10 transition-colors"
                                        title="Excluir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $transactions->links() }}</div>
    @endif
</x-app-layout>
