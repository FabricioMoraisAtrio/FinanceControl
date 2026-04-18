<x-app-layout>
    <x-slot name="title">Contas em Aberto — FinanceControl</x-slot>

    <div class="flex items-center justify-between mb-6 print:mb-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Contas em Aberto</h1>
            <p class="text-sm text-slate-500 mt-0.5">Parcelamentos futuros em andamento</p>
        </div>
        <div class="flex items-center gap-3 print:hidden">
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 border border-slate-700 hover:bg-slate-800 text-slate-300 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </button>
            <a href="{{ route('transactions.create') }}"
                class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Novo Parcelamento
            </a>
        </div>
    </div>

    {{-- Filtros de pagamento --}}
    <div class="flex items-center gap-2 mb-6 print:hidden">
        <a href="{{ route('open-bills.index') }}"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors
                {{ !$filterType ? 'bg-slate-700 text-white' : 'text-slate-400 border border-slate-700 hover:bg-slate-800' }}">
            Todos
        </a>
        <a href="{{ route('open-bills.index', ['payment_type' => 'credit']) }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-colors
                {{ $filterType === 'credit' ? 'bg-blue-600 text-white' : 'text-slate-400 border border-slate-700 hover:bg-slate-800' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            Crédito
        </a>
        <a href="{{ route('open-bills.index', ['payment_type' => 'debit']) }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-colors
                {{ $filterType === 'debit' ? 'bg-emerald-600 text-white' : 'text-slate-400 border border-slate-700 hover:bg-slate-800' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            Débito
        </a>
    </div>

    @if($openBills->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-16 text-center">
            <div class="w-16 h-16 bg-emerald-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-slate-400 font-semibold">Nenhuma conta em aberto!</p>
            <p class="text-slate-600 text-sm mt-1">Todos os parcelamentos estão quitados.</p>
        </div>
    @else

    {{-- Cards de Resumo --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-red-500/20 rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Total em Aberto</p>
            <p class="text-2xl font-bold text-red-400">R$ {{ number_format($totalRemaining, 2, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $openBills->count() }} dívida(s) ativas</p>
        </div>
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-orange-500/20 rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Parcelas / Mês</p>
            <p class="text-2xl font-bold text-orange-400">R$ {{ number_format($totalMonthly, 2, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-1">compromisso mensal</p>
        </div>
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-emerald-500/20 rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Salário do Mês</p>
            <p class="text-2xl font-bold text-emerald-400">R$ {{ number_format($grossSalary, 2, ',', '.') }}</p>
            @if($grossSalary > 0)
                <p class="text-xs text-slate-500 mt-1">
                    {{ number_format(($totalMonthly / $grossSalary) * 100, 1) }}% comprometido
                </p>
            @else
                <p class="text-xs text-slate-600 mt-1">Nenhum salário registrado</p>
            @endif
        </div>
    </div>

    {{-- Tabela principal --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="font-semibold text-white">Parcelamentos Futuros</h2>
            <span class="text-xs text-slate-500">Atualizado {{ now()->format('d/m/Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-800">
                    <tr class="text-xs text-slate-500 font-semibold uppercase tracking-widest">
                        <th class="text-left px-5 py-3.5">Descrição</th>
                        <th class="text-right px-4 py-3.5">Total Restante</th>
                        <th class="text-center px-4 py-3.5">Parcelas</th>
                        <th class="text-right px-4 py-3.5">Valor/Mês</th>
                        <th class="text-center px-4 py-3.5">Próxima</th>
                        <th class="text-center px-4 py-3.5">Termina</th>
                        <th class="text-left px-4 py-3.5 hidden md:table-cell">Categoria</th>
                        <th class="text-left px-4 py-3.5 hidden lg:table-cell">Conta</th>
                        <th class="px-4 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @php $shownThirdPartyHeader = false; @endphp
                    @foreach($openBills as $bill)
                    @php
                        $monthsLeft = $bill['remaining_count'];
                        $urgency    = $bill['end_date']->diffInMonths(now());
                        $rowColor   = match(true) {
                            $bill['payer'] !== null => 'bg-blue-500/5',
                            $monthsLeft === 1       => 'bg-emerald-500/5',
                            $urgency <= 2           => 'bg-yellow-500/5',
                            default                 => '',
                        };
                    @endphp

                    {{-- Separador: início das parcelas de terceiros --}}
                    @if($bill['payer'] !== null && !$shownThirdPartyHeader)
                        @php $shownThirdPartyHeader = true; @endphp
                        <tr>
                            <td colspan="9" class="px-5 py-2 bg-slate-800/80">
                                <span class="text-xs font-semibold text-blue-400 uppercase tracking-widest">Parcelas de Terceiros</span>
                            </td>
                        </tr>
                    @endif

                    <tr class="hover:bg-slate-800/40 transition-colors {{ $rowColor }}">
                        <td class="px-5 py-3.5">
                            <div>
                                <span class="font-semibold text-slate-200">{{ $bill['name'] }}</span>
                                @if($bill['payer'])
                                    <span class="ml-2 text-xs bg-blue-500/15 text-blue-400 px-1.5 py-0.5 rounded font-medium">
                                        {{ $bill['payer'] }}
                                    </span>
                                @endif
                            </div>
                            @if($bill['category'])
                                <p class="text-xs text-slate-600 mt-0.5 md:hidden">{{ $bill['category']->name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <span class="font-bold text-red-400">R$ {{ number_format($bill['total_remaining'], 2, ',', '.') }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-block bg-slate-800 text-slate-300 font-bold text-sm px-3 py-1 rounded-lg min-w-[2.5rem]">
                                {{ $bill['remaining_count'] }}x
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <span class="font-semibold text-orange-400">R$ {{ number_format($bill['amount_per'], 2, ',', '.') }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-center text-xs whitespace-nowrap">
                            @if($bill['overdue'])
                                <span class="inline-flex items-center gap-1 bg-red-500/15 text-red-400 px-2 py-0.5 rounded-full font-semibold">
                                    ⚠ {{ $bill['next_date']->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-slate-400">{{ $bill['next_date']->format('d/m/Y') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            @php
                                $endDate   = $bill['end_date'];
                                $daysLeft  = now()->diffInDays($endDate, false);
                                $endClass  = $daysLeft <= 30  ? 'text-yellow-400 font-semibold'
                                           : ($daysLeft <= 90 ? 'text-orange-400' : 'text-slate-400');
                            @endphp
                            <span class="text-xs {{ $endClass }}">{{ $endDate->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-4 py-3.5 hidden md:table-cell">
                            @if($bill['category'])
                                <span class="text-xs text-slate-500">{{ $bill['category']->name }}</span>
                            @else
                                <span class="text-xs text-slate-700">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 hidden lg:table-cell">
                            <span class="text-xs text-slate-500">{{ $bill['account']->name }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <a href="{{ route('transactions.index', ['installment_group' => $bill['group_id'], 'month' => now()->month, 'year' => now()->year]) }}"
                                class="p-1.5 text-slate-600 hover:text-slate-300 rounded-lg hover:bg-slate-700 transition-colors inline-block"
                                title="Ver parcelas">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                {{-- Rodapé totalizador --}}
                @php
                    $myBills    = $openBills->filter(fn($b) => $b['payer'] === null);
                    $theirBills = $openBills->filter(fn($b) => $b['payer'] !== null);
                @endphp
                <tfoot class="border-t-2 border-slate-700">
                    @if($myBills->isNotEmpty())
                    <tr class="bg-slate-800/40">
                        <td class="px-5 py-2.5 text-xs font-semibold text-slate-400">TOTAL MEUS</td>
                        <td class="px-4 py-2.5 text-right text-sm font-bold text-red-400">
                            R$ {{ number_format($myBills->sum('total_remaining'), 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-2.5 text-center text-xs text-slate-500">{{ $myBills->count() }}x</td>
                        <td class="px-4 py-2.5 text-right text-sm font-bold text-orange-400">
                            R$ {{ number_format($myBills->sum('amount_per'), 2, ',', '.') }}
                        </td>
                        <td colspan="5"></td>
                    </tr>
                    @endif
                    @if($theirBills->isNotEmpty())
                    <tr class="bg-blue-500/5">
                        <td class="px-5 py-2.5 text-xs font-semibold text-blue-400">TOTAL TERCEIROS</td>
                        <td class="px-4 py-2.5 text-right text-sm font-bold text-blue-400">
                            R$ {{ number_format($theirBills->sum('total_remaining'), 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-2.5 text-center text-xs text-slate-500">{{ $theirBills->count() }}x</td>
                        <td class="px-4 py-2.5 text-right text-sm font-bold text-blue-400">
                            R$ {{ number_format($theirBills->sum('amount_per'), 2, ',', '.') }}
                        </td>
                        <td colspan="5"></td>
                    </tr>
                    @endif
                    <tr class="bg-slate-800/80 border-t border-slate-700">
                        <td class="px-5 py-3 font-bold text-white text-sm">TOTAL GERAL</td>
                        <td class="px-4 py-3 text-right font-bold text-red-400">
                            R$ {{ number_format($totalRemaining, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-slate-500">{{ $openBills->count() }} dívidas</td>
                        <td class="px-4 py-3 text-right font-bold text-orange-400">
                            R$ {{ number_format($totalMonthly, 2, ',', '.') }}
                        </td>
                        <td colspan="5"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Barra de progresso por prazo --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-4">Distribuição por Prazo</h3>
        <div class="space-y-3">
            @foreach($openBills->sortBy('end_date') as $bill)
            @php
                $pct = min(100, max(5,
                    ($bill['installment_total'] - $bill['remaining_count'] + 1) / max(1, $bill['installment_total']) * 100
                ));
                $barColor = $bill['remaining_count'] === 1 ? 'bg-emerald-500'
                          : ($bill['remaining_count'] <= 3  ? 'bg-yellow-400'
                          : ($bill['remaining_count'] <= 6  ? 'bg-orange-400' : 'bg-red-500'));
            @endphp
            <div>
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-medium text-slate-300">{{ $bill['name'] }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-slate-500">{{ $bill['remaining_count'] }}x restantes · até {{ $bill['end_date']->format('m/Y') }}</span>
                        <span class="font-semibold text-slate-200">R$ {{ number_format($bill['total_remaining'], 2, ',', '.') }}</span>
                    </div>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $barColor }} transition-all" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4 flex items-center gap-5 text-xs text-slate-600">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Última parcela</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span> ≤ 3 meses</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-400 inline-block"></span> ≤ 6 meses</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> > 6 meses</span>
        </div>
    </div>

    {{-- Dica sobre responsável --}}
    <p class="text-xs text-slate-700 mt-4 text-center print:hidden">
        Dica: para marcar o responsável de uma parcela, comece as observações com <code class="text-slate-600">[@Nome]</code> — ex: <code class="text-slate-600">[@Pai]</code>
    </p>

    @endif
</x-app-layout>
