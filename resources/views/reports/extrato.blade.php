<x-app-layout>
    <x-slot name="title">Extrato Mensal — FinanceControl</x-slot>

    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between mb-6 print:mb-4">
        <div>
            <a href="{{ route('reports.index') }}" class="text-xs text-slate-500 hover:text-slate-300 transition-colors print:hidden">← Relatórios</a>
            <h1 class="text-2xl font-bold text-white mt-1 print:text-black">Extrato Mensal</h1>
        </div>
        <button onclick="window.print()" class="print:hidden inline-flex items-center gap-2 border border-slate-700 hover:bg-slate-800 text-slate-300 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimir / PDF
        </button>
    </div>

    {{-- Seletor de período --}}
    <form method="GET" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 mb-6 flex flex-wrap gap-3 items-end print:hidden">
        <div>
            <label class="block text-xs text-slate-500 mb-1 font-medium">Mês</label>
            <select name="month" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $monthNames[$m] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1 font-medium">Ano</label>
            <select name="year" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @foreach(range(now()->year - 3, now()->year + 1) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold rounded-lg px-4 py-2 transition-colors">
            Gerar Extrato
        </button>
    </form>

    {{-- Resumo do mês --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-slate-900 border border-emerald-500/20 rounded-xl px-4 py-4">
            <p class="text-xs text-slate-500 mb-1 uppercase tracking-wide">Entradas</p>
            <p class="text-lg font-bold text-emerald-400">R$ {{ number_format($totalIncome, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-900 border border-red-500/20 rounded-xl px-4 py-4">
            <p class="text-xs text-slate-500 mb-1 uppercase tracking-wide">Saídas</p>
            <p class="text-lg font-bold text-red-400">R$ {{ number_format($totalExpense, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-900 border border-blue-500/20 rounded-xl px-4 py-4">
            <p class="text-xs text-slate-500 mb-1 uppercase tracking-wide">Transferências</p>
            <p class="text-lg font-bold text-blue-400">R$ {{ number_format($totalTransfer, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-4">
            <p class="text-xs text-slate-500 mb-1 uppercase tracking-wide">Resultado</p>
            @php $isPositive = $balance >= 0; @endphp
            <p class="text-lg font-bold {{ $isPositive ? 'text-white' : 'text-red-400' }}">
                {{ $isPositive ? '+' : '' }}R$ {{ number_format($balance, 2, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Título do período --}}
    <div class="flex items-center gap-3 mb-4">
        <h2 class="text-base font-semibold text-slate-300">{{ $monthNames[$month] }} de {{ $year }}</h2>
        @if($byDay->isEmpty())
            <span class="text-xs text-slate-600">— Nenhum lançamento</span>
        @else
            <span class="text-xs text-slate-600">{{ $byDay->flatten()->count() }} lançamento(s)</span>
        @endif
    </div>

    @if($byDay->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center">
            <p class="text-slate-500 text-sm">Nenhum lançamento neste período.</p>
            <a href="{{ route('transactions.create') }}" class="inline-block mt-3 text-sm text-emerald-400 hover:text-emerald-300 print:hidden">
                Adicionar lançamento →
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($byDay as $day => $dayTransactions)
            @php
                $dayIncome   = $dayTransactions->where('type','income')->sum('amount');
                $dayExpense  = $dayTransactions->where('type','expense')->sum('amount');
                $dayDate     = \Carbon\Carbon::parse($day);
            @endphp
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                {{-- Cabeçalho do dia --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-800 bg-slate-800/40">
                    <div class="flex items-center gap-3">
                        <div class="text-center">
                            <p class="text-xl font-bold text-white leading-none">{{ $dayDate->format('d') }}</p>
                            <p class="text-xs text-slate-500 uppercase">{{ $dayDate->format('D') }}</p>
                        </div>
                        <div class="w-px h-8 bg-slate-700"></div>
                        <p class="text-xs text-slate-500">{{ $dayDate->translatedFormat('l') }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs">
                        @if($dayIncome > 0)
                            <span class="text-emerald-400 font-semibold">+R$ {{ number_format($dayIncome, 2, ',', '.') }}</span>
                        @endif
                        @if($dayExpense > 0)
                            <span class="text-red-400 font-semibold">-R$ {{ number_format($dayExpense, 2, ',', '.') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Transações do dia --}}
                <div class="divide-y divide-slate-800/60">
                    @foreach($dayTransactions as $t)
                    <div class="flex items-center gap-3 px-5 py-3">
                        {{-- Ícone tipo --}}
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                            {{ $t->type === 'income' ? 'bg-emerald-500/15' : ($t->type === 'expense' ? 'bg-red-500/15' : 'bg-blue-500/15') }}">
                            @if($t->type === 'income')
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            @elseif($t->type === 'expense')
                                <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            @else
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            @endif
                        </div>

                        {{-- Descrição --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-sm font-medium text-slate-200">{{ $t->description }}</span>

                                @if($t->isInstallment())
                                    <span class="text-xs bg-orange-500/15 text-orange-400 px-1.5 py-0.5 rounded font-semibold">{{ $t->installmentLabel() }}</span>
                                @endif
                                @if($t->is_fixed)
                                    <span class="text-xs bg-purple-500/15 text-purple-400 px-1.5 py-0.5 rounded font-medium">Fixa</span>
                                @endif
                                @if($t->account->type === 'credit_card')
                                    <span class="text-xs bg-blue-500/10 text-blue-400 px-1.5 py-0.5 rounded font-medium">💳 Crédito</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-slate-500">{{ $t->account->name }}</span>
                                @if($t->category)
                                    <span class="text-slate-700">·</span>
                                    <span class="text-xs text-slate-500">{{ $t->category->name }}</span>
                                @endif
                                @if($t->notes)
                                    <span class="text-slate-700">·</span>
                                    <span class="text-xs text-slate-600 italic truncate max-w-xs">{{ $t->notes }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Valor --}}
                        <p class="text-sm font-bold shrink-0
                            {{ $t->type === 'income' ? 'text-emerald-400' : ($t->type === 'expense' ? 'text-red-400' : 'text-blue-400') }}">
                            {{ $t->type === 'expense' ? '-' : '+' }}R$ {{ number_format($t->amount, 2, ',', '.') }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        {{-- Rodapé do extrato --}}
        <div class="mt-6 bg-slate-900 border border-slate-800 rounded-2xl px-5 py-4 flex items-center justify-between">
            <div class="text-xs text-slate-500">
                Extrato de {{ $monthNames[$month] }}/{{ $year }} · {{ $byDay->flatten()->count() }} lançamento(s)
            </div>
            <div class="flex items-center gap-6 text-sm">
                <div class="text-right">
                    <p class="text-xs text-slate-500">Entradas</p>
                    <p class="font-bold text-emerald-400">+R$ {{ number_format($totalIncome, 2, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-500">Saídas</p>
                    <p class="font-bold text-red-400">-R$ {{ number_format($totalExpense, 2, ',', '.') }}</p>
                </div>
                <div class="text-right border-l border-slate-700 pl-6">
                    <p class="text-xs text-slate-500">Resultado</p>
                    <p class="font-bold {{ $balance >= 0 ? 'text-white' : 'text-red-400' }}">
                        {{ $balance >= 0 ? '+' : '' }}R$ {{ number_format($balance, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
