<x-app-layout>
    <x-slot name="title">Orçamentos — FinanceControl</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Orçamentos</h1>
        <p class="text-sm text-slate-500 mt-0.5">Defina limites de gasto por categoria</p>
    </div>

    {{-- Navegação de mês --}}
    @php
        $prevMonth = \Carbon\Carbon::create($year, $month)->subMonth();
        $nextMonth = \Carbon\Carbon::create($year, $month)->addMonth();
        $monthNames = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    @endphp
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('budgets.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
            class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <span class="font-semibold text-slate-200">{{ $monthNames[$month] }} {{ $year }}</span>
        <a href="{{ route('budgets.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
            class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Lista de Orçamentos --}}
        <div class="lg:col-span-2 space-y-3">
            @if($budgets->isEmpty())
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center">
                    <p class="text-slate-500 text-sm">Nenhum orçamento definido para este mês.</p>
                </div>
            @else
                @foreach($budgets as $budget)
                @php $pct = $budget->getPercentage(); $spent = $budget->getSpent(); @endphp
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 hover:border-slate-700 transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $budget->category->color }}"></div>
                            <span class="font-semibold text-slate-200">{{ $budget->category->name }}</span>
                        </div>
                        <form action="{{ route('budgets.destroy', $budget) }}" method="POST" onsubmit="return confirm('Remover orçamento?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-slate-600 hover:text-red-400 transition-colors">Remover</button>
                        </form>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2.5 mb-3">
                        <div class="h-2.5 rounded-full transition-all
                            {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-yellow-400' : 'bg-emerald-500') }}"
                            style="width: {{ min(100, $pct) }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Gasto: <span class="{{ $pct >= 100 ? 'text-red-400 font-bold' : 'text-slate-300 font-semibold' }}">R$ {{ number_format($spent, 2, ',', '.') }}</span></span>
                        <span class="text-slate-500">Limite: <span class="text-slate-300 font-semibold">R$ {{ number_format($budget->amount, 2, ',', '.') }}</span></span>
                        <span class="{{ $pct >= 100 ? 'text-red-400 font-bold' : ($pct >= 80 ? 'text-yellow-400' : 'text-slate-500') }}">{{ number_format($pct, 0) }}%</span>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        {{-- Formulário --}}
        <div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sticky top-6">
                <h2 class="font-semibold text-white mb-4">Definir Orçamento</h2>
                <form action="{{ route('budgets.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Categoria</label>
                        <select name="category_id" required
                            class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Selecione...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Valor Limite (R$)</label>
                        <input type="number" name="amount" step="0.01" min="0.01" required
                            class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 placeholder-slate-600"
                            placeholder="0,00">
                        @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit"
                        class="w-full bg-emerald-500 hover:bg-emerald-400 text-white font-bold py-2.5 rounded-xl transition-colors text-sm">
                        Salvar Orçamento
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
