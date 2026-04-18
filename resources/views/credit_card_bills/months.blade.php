<x-app-layout>
    <x-slot name="title">Histórico de Faturas — {{ $account->name }}</x-slot>

    <div class="mb-6">
        <a href="{{ route('bills.index') }}" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">← Faturas</a>
        <div class="flex items-center justify-between mt-1">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                     style="background-color:{{ $account->color }}20">
                    <div class="w-3 h-3 rounded-full" style="background-color:{{ $account->color }}"></div>
                </div>
                <h1 class="text-2xl font-bold text-white">{{ $account->name }}</h1>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-500">Total gasto no cartão</p>
                <p class="text-2xl font-bold text-blue-400">R$ {{ number_format($totalAllTime, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($months as $month)
        @php
            $isOpen   = $month['status'] === 'open';
            $isPaid   = $month['status'] === 'paid';
            $isClosed = $month['status'] === 'closed';

            $borderColor = $isOpen   ? 'border-blue-500/40'
                         : ($isPaid   ? 'border-slate-700'
                         : 'border-orange-500/30');
            $amountColor = $isOpen   ? 'text-blue-400'
                         : ($isPaid   ? 'text-slate-300'
                         : 'text-orange-400');
            $bgColor     = $isOpen   ? 'bg-blue-500/5'
                         : ($isPaid   ? 'bg-slate-900'
                         : 'bg-orange-500/5');
        @endphp

        <div class="{{ $bgColor }} border {{ $borderColor }} rounded-2xl p-5 flex flex-col gap-3">

            {{-- Mês + status --}}
            <div class="flex items-center justify-between">
                <span class="text-base font-bold text-white">{{ $month['label'] }}</span>
                @if($isOpen)
                    <span class="text-xs bg-blue-500/15 text-blue-400 px-2 py-0.5 rounded-full font-semibold">Aberto</span>
                @elseif($isPaid)
                    <span class="text-xs bg-emerald-500/15 text-emerald-400 px-2 py-0.5 rounded-full font-semibold">✓ Paga</span>
                @else
                    <span class="text-xs bg-orange-500/15 text-orange-400 px-2 py-0.5 rounded-full font-semibold">Pendente</span>
                @endif
            </div>

            {{-- Valor --}}
            <p class="text-2xl font-bold {{ $amountColor }}">
                R$ {{ number_format($month['total'], 2, ',', '.') }}
            </p>

            {{-- Datas --}}
            <div class="text-xs text-slate-600 space-y-0.5">
                <p>{{ $month['period_start']->format('d/m') }} – {{ $month['period_end']->format('d/m/Y') }}</p>
                <p class="text-slate-500">Vence {{ $month['due_date']->format('d/m/Y') }}</p>
            </div>

            {{-- Ações --}}
            <div class="mt-auto pt-3 border-t border-slate-800 space-y-2">
                <a href="{{ route('credit-card-bills.statement', ['account' => $account, 'period_start' => $month['period_start']->format('Y-m-d'), 'period_end' => $month['period_end']->format('Y-m-d')]) }}"
                    class="block text-center text-xs text-slate-400 hover:text-blue-400 transition-colors py-1.5 rounded-lg hover:bg-slate-800">
                    Ver extrato
                </a>
                @if($isClosed && $month['bill'])
                <form action="{{ route('credit-card-bills.pay', $month['bill']) }}" method="POST"
                    onsubmit="return confirm('Confirmar pagamento desta fatura?')">
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="w-full text-xs bg-emerald-500 hover:bg-emerald-400 text-white font-semibold py-1.5 rounded-lg transition-colors">
                        Marcar Paga
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($months->isEmpty())
    <div class="text-center py-16 text-slate-600">Nenhuma fatura encontrada.</div>
    @endif
</x-app-layout>
