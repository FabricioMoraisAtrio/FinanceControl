<x-app-layout>
    <x-slot name="title">Relatórios — FinanceControl</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Relatórios</h1>
        <a href="{{ route('reports.extrato', ['month' => now()->month, 'year' => now()->year]) }}"
            class="inline-flex items-center gap-2 border border-slate-700 hover:bg-slate-800 text-slate-300 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Extrato Mensal
        </a>
    </div>

    {{-- Filtro --}}
    <form method="GET" class="flex items-center gap-3 mb-6">
        @php $months = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro']; @endphp
        <select name="month" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $months[$m] }}</option>
            @endforeach
        </select>
        <select name="year" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            @foreach(range(now()->year - 2, now()->year) as $y)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            Ver
        </button>
    </form>

    {{-- Resumo do Mês --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-slate-900 border border-emerald-500/20 rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Entradas</p>
            <p class="text-xl font-bold text-emerald-400">R$ {{ number_format($totalIncome, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-900 border border-red-500/20 rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Saídas</p>
            <p class="text-xl font-bold text-red-400">R$ {{ number_format($totalExpense, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Saldo</p>
            <p class="text-xl font-bold {{ $balance >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                R$ {{ number_format($balance, 2, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Saídas por Categoria --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <h2 class="font-semibold text-white mb-5">Saídas por Categoria</h2>
            @if($expenseByCategory->isEmpty())
                <p class="text-slate-600 text-sm text-center py-8">Nenhuma saída neste mês.</p>
            @else
                <div class="space-y-4">
                    @foreach($expenseByCategory as $item)
                    @php $pct = $totalExpense > 0 ? ($item['total'] / $totalExpense) * 100 : 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $item['color'] }}"></div>
                                <span class="text-slate-300">{{ $item['name'] }}</span>
                            </div>
                            <span class="font-semibold text-slate-200">R$ {{ number_format($item['total'], 2, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-2">
                            <div class="h-2 rounded-full" style="width: {{ $pct }}%; background-color: {{ $item['color'] }}"></div>
                        </div>
                        <p class="text-right text-xs text-slate-600 mt-0.5">{{ number_format($pct, 1) }}%</p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Entradas por Categoria --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <h2 class="font-semibold text-white mb-5">Entradas por Categoria</h2>
            @if($incomeByCategory->isEmpty())
                <p class="text-slate-600 text-sm text-center py-8">Nenhuma entrada neste mês.</p>
            @else
                <div class="space-y-4">
                    @foreach($incomeByCategory as $item)
                    @php $pct = $totalIncome > 0 ? ($item['total'] / $totalIncome) * 100 : 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $item['color'] }}"></div>
                                <span class="text-slate-300">{{ $item['name'] }}</span>
                            </div>
                            <span class="font-semibold text-slate-200">R$ {{ number_format($item['total'], 2, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-2">
                            <div class="h-2 rounded-full" style="width: {{ $pct }}%; background-color: {{ $item['color'] }}"></div>
                        </div>
                        <p class="text-right text-xs text-slate-600 mt-0.5">{{ number_format($pct, 1) }}%</p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Evolução Anual --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
        <h2 class="font-semibold text-white mb-5">Evolução {{ $year }}</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-500 font-semibold uppercase tracking-wide border-b border-slate-800">
                        <th class="text-left py-2.5 pb-3">Mês</th>
                        <th class="text-right py-2.5 pb-3">Entradas</th>
                        <th class="text-right py-2.5 pb-3">Saídas</th>
                        <th class="text-right py-2.5 pb-3">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @foreach($monthlyData as $m => $data)
                    @php $bal = $data['income'] - $data['expense']; @endphp
                    <tr class="{{ $m == $month ? 'bg-emerald-500/5' : '' }}">
                        <td class="py-2.5 font-medium {{ $m == $month ? 'text-emerald-400' : 'text-slate-400' }}">
                            {{ $months[$m] }}
                            @if($m == $month)<span class="ml-1 text-xs opacity-60">← atual</span>@endif
                        </td>
                        <td class="py-2.5 text-right text-emerald-400">R$ {{ number_format($data['income'], 2, ',', '.') }}</td>
                        <td class="py-2.5 text-right text-red-400">R$ {{ number_format($data['expense'], 2, ',', '.') }}</td>
                        <td class="py-2.5 text-right font-bold {{ $bal >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            R$ {{ number_format($bal, 2, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
