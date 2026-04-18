<x-app-layout>
    <x-slot name="title">Fatura {{ $account->name }} — {{ $periodStart->format('d/m') }} a {{ $periodEnd->format('d/m/Y') }}</x-slot>

    {{-- Cabeçalho --}}
    <div class="flex items-start justify-between mb-6 print:mb-3">
        <div>
            <a href="{{ route('credit-card-bills.index', $account) }}" class="text-xs text-slate-500 hover:text-slate-300 transition-colors print:hidden">
                ← Fatura {{ $account->name }}
            </a>
            <div class="flex items-center gap-3 mt-1">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background-color:{{ $account->color }}25">
                    <div class="w-3 h-3 rounded-full" style="background-color:{{ $account->color }}"></div>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">{{ $account->name }}</h1>
                    <p class="text-xs text-slate-500">
                        Fatura de {{ $periodStart->format('d/m/Y') }} a {{ $periodEnd->format('d/m/Y') }}
                    </p>
                </div>
            </div>
        </div>
        <button onclick="window.print()"
            class="print:hidden inline-flex items-center gap-2 border border-slate-700 hover:bg-slate-800 text-slate-300 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimir / PDF
        </button>
    </div>

    {{-- Seletor de período --}}
    <form method="GET" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 mb-6 print:hidden">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1 font-medium">Período</label>
                <select name="period_start" onchange="updateEnd(this)"
                    class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($periods as $p)
                        <option value="{{ $p['start']->format('Y-m-d') }}"
                            data-end="{{ $p['end']->format('Y-m-d') }}"
                            {{ $periodStart->format('Y-m-d') === $p['start']->format('Y-m-d') ? 'selected' : '' }}>
                            {{ $p['start']->format('d/m/Y') }} – {{ $p['end']->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="period_end" id="period_end" value="{{ $periodEnd->format('Y-m-d') }}">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg px-4 py-2 transition-colors">
                Ver Fatura
            </button>
        </div>
    </form>

    @if($transactions->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center">
            <p class="text-slate-500 text-sm">Nenhum lançamento neste período.</p>
        </div>
    @else

    {{-- Resumo topo --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="sm:col-span-2 bg-gradient-to-br from-slate-800 to-slate-900 border border-blue-500/30 rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Total da Fatura</p>
            <p class="text-3xl font-bold text-blue-400">R$ {{ number_format($total, 2, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $transactions->count() }} lançamento(s) · {{ $byDay->count() }} dia(s) com gastos</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <p class="text-xs text-slate-500 uppercase tracking-widest mb-1">Maior gasto</p>
            @php $biggest = $transactions->sortByDesc('amount')->first(); @endphp
            <p class="text-lg font-bold text-slate-200">R$ {{ number_format($biggest->amount, 2, ',', '.') }}</p>
            <p class="text-xs text-slate-500 truncate mt-0.5">{{ $biggest->description }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <p class="text-xs text-slate-500 uppercase tracking-widest mb-1">Média/dia</p>
            <p class="text-lg font-bold text-slate-200">R$ {{ number_format($total / max(1, $byDay->count()), 2, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-0.5">dias com gastos</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Coluna esquerda: extrato por dia --}}
        <div class="lg:col-span-2 space-y-3">
            @foreach($byDay as $day => $dayTransactions)
            @php
                $dayTotal = $dayTransactions->sum('amount');
                $dayDate  = \Carbon\Carbon::parse($day);
            @endphp
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                {{-- Cabeçalho do dia --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-800/80 bg-slate-800/30">
                    <div class="flex items-center gap-3">
                        <div class="text-center w-8">
                            <p class="text-lg font-bold text-white leading-none">{{ $dayDate->format('d') }}</p>
                            <p class="text-xs text-slate-500">{{ $dayDate->translatedFormat('D') }}</p>
                        </div>
                        <div class="w-px h-7 bg-slate-700"></div>
                        <p class="text-xs text-slate-500 capitalize">{{ $dayDate->translatedFormat('l, F') }}</p>
                    </div>
                    <span class="text-sm font-bold text-red-400">
                        -R$ {{ number_format($dayTotal, 2, ',', '.') }}
                    </span>
                </div>

                {{-- Itens do dia --}}
                <div class="divide-y divide-slate-800/50">
                    @foreach($dayTransactions as $t)
                    <div class="flex items-center gap-3 px-5 py-3">
                        {{-- Ícone da categoria --}}
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                            style="background-color: {{ ($t->category?->color ?? '#6B7280') }}20">
                            <div class="w-2.5 h-2.5 rounded-full"
                                style="background-color: {{ $t->category?->color ?? '#6B7280' }}"></div>
                        </div>

                        {{-- Descrição --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-200 truncate">{{ $t->description }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-slate-600">{{ $t->category?->name ?? 'Sem categoria' }}</span>
                                @if($t->isInstallment())
                                    <span class="text-xs bg-orange-500/15 text-orange-400 px-1.5 py-0.5 rounded font-semibold">
                                        {{ $t->installmentLabel() }}
                                    </span>
                                @endif
                                @if($t->notes)
                                    <span class="text-xs text-slate-600 italic truncate">· {{ $t->notes }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Valor --}}
                        <p class="text-sm font-bold text-red-400 shrink-0">
                            -R$ {{ number_format($t->amount, 2, ',', '.') }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- Total final --}}
            <div class="bg-slate-900 border border-blue-500/20 rounded-2xl px-5 py-4 flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-400">Total da fatura
                    <span class="text-slate-600 font-normal">
                        · {{ $periodStart->format('d/m') }} a {{ $periodEnd->format('d/m/Y') }}
                    </span>
                </p>
                <p class="text-xl font-bold text-blue-400">R$ {{ number_format($total, 2, ',', '.') }}</p>
            </div>
        </div>

        {{-- Coluna direita: por categoria --}}
        <div class="space-y-4">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-4">Por Categoria</h3>
                <div class="space-y-3">
                    @foreach($byCategory as $cat)
                    @php $pct = $total > 0 ? ($cat['total'] / $total) * 100 : 0; @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color:{{ $cat['color'] }}"></div>
                                <span class="text-slate-300 truncate">{{ $cat['name'] }}</span>
                                <span class="text-xs text-slate-600 shrink-0">{{ $cat['count'] }}x</span>
                            </div>
                            <span class="font-semibold text-slate-200 shrink-0 ml-2">
                                R$ {{ number_format($cat['total'], 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full" style="width:{{ $pct }}%; background-color:{{ $cat['color'] }}"></div>
                        </div>
                        <p class="text-right text-xs text-slate-700 mt-0.5">{{ number_format($pct, 1) }}%</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Faturas fechadas --}}
            @if($closedBills->isNotEmpty())
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 print:hidden">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Faturas Anteriores</h3>
                <div class="space-y-2">
                    @foreach($closedBills->take(6) as $bill)
                    <a href="{{ route('credit-card-bills.statement', [$account, 'period_start' => $bill->period_start->format('Y-m-d'), 'period_end' => $bill->period_end->format('Y-m-d')]) }}"
                        class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-slate-800 transition-colors group">
                        <div>
                            <p class="text-xs font-medium text-slate-400 group-hover:text-slate-200">
                                {{ $bill->period_start->format('d/m') }} – {{ $bill->period_end->format('d/m/Y') }}
                            </p>
                            <span class="text-xs {{ $bill->isPaid() ? 'text-emerald-600' : 'text-orange-500' }}">
                                {{ $bill->isPaid() ? '✓ Paga' : 'Pendente' }}
                            </span>
                        </div>
                        <span class="text-sm font-bold text-slate-300">R$ {{ number_format($bill->total_amount, 2, ',', '.') }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    @endif

    <script>
    function updateEnd(select) {
        const opt = select.options[select.selectedIndex];
        document.getElementById('period_end').value = opt.dataset.end;
    }
    </script>
</x-app-layout>
