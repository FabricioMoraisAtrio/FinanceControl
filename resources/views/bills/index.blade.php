<x-app-layout>
    <x-slot name="title">Faturas — FinanceControl</x-slot>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Faturas</h1>
            <p class="text-sm text-slate-500 mt-0.5">Cartões de crédito e faturas em aberto</p>
        </div>
        <a href="{{ route('accounts.create') }}"
            class="inline-flex items-center gap-2 border border-slate-700 hover:bg-slate-800 text-slate-300 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            + Novo Cartão
        </a>
    </div>

    @if($cards->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-16 text-center">
            <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <p class="text-slate-400 font-semibold">Nenhum cartão de crédito cadastrado</p>
            <a href="{{ route('accounts.create') }}" class="inline-block mt-3 text-sm text-blue-400 hover:text-blue-300">
                Adicionar cartão →
            </a>
        </div>
    @else
        <div class="space-y-10">
            @foreach($cards as $card)
            @php
                $account     = $card['account'];
                $daysToClose = $card['days_to_close'];
                $urgencyBorder = $daysToClose <= 3 ? 'border-red-500/50'
                               : ($daysToClose <= 7 ? 'border-yellow-500/40' : 'border-blue-500/30');
            @endphp

            <div class="space-y-3">

                {{-- Card 1: Total histórico → abre view de meses --}}
                <a href="{{ route('credit-card-bills.months', $account) }}"
                    class="block group relative overflow-hidden rounded-3xl border {{ $urgencyBorder }} shadow-xl
                           bg-gradient-to-br from-slate-800 via-slate-900 to-slate-900
                           hover:scale-[1.01] transition-all duration-200">

                    <div class="absolute inset-0 opacity-10 pointer-events-none"
                         style="background: radial-gradient(ellipse at top right, {{ $account->color }}, transparent 65%)"></div>

                    <div class="relative px-8 py-8 flex flex-col sm:flex-row items-center sm:justify-between gap-6">

                        {{-- Nome --}}
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-10 rounded-xl flex items-center justify-center shrink-0"
                                 style="background: linear-gradient(135deg, {{ $account->color }}30, {{ $account->color }}10);
                                        border: 1px solid {{ $account->color }}40">
                                <svg class="w-6 h-6" fill="none" stroke="{{ $account->color }}" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white group-hover:text-blue-300 transition-colors">
                                    {{ $account->name }}
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Fecha dia {{ $account->closing_day ?? 21 }} &middot; Vence dia {{ $account->payment_day ?? 10 }}
                                </p>
                            </div>
                        </div>

                        {{-- Total histórico + barra de limite --}}
                        @php
                            $spent = $card['total_all_time'];
                            $limit = (float) ($account->credit_limit ?? 0);
                            $pct   = $limit > 0 ? min(100, round($spent / $limit * 100)) : null;
                            $barColor = $pct === null ? 'bg-blue-500'
                                      : ($pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-blue-500'));
                        @endphp
                        <div class="text-center min-w-[220px]">
                            <p class="text-xs text-slate-500 uppercase tracking-widest mb-1">Total gasto no cartão</p>
                            <p class="text-5xl font-bold text-blue-400 tracking-tight">
                                R$ {{ number_format($spent, 2, ',', '.') }}
                            </p>
                            @if($limit > 0)
                                <div class="mt-3 px-2">
                                    <div class="flex justify-between text-xs text-slate-500 mb-1">
                                        <span>{{ $pct }}% utilizado</span>
                                        <span>Limite R$ {{ number_format($limit, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-700/60 rounded-full">
                                        <div class="h-2 rounded-full {{ $barColor }} transition-all"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @else
                                <p class="text-xs text-slate-500 mt-1.5">Ver histórico por mês →</p>
                            @endif
                        </div>

                        {{-- Info direita --}}
                        <div class="flex items-center gap-5">
                            <div class="text-right space-y-1.5 text-xs">
                                <div class="flex justify-between gap-5">
                                    <span class="text-slate-500">Fatura atual</span>
                                    <span class="font-semibold text-blue-400">R$ {{ number_format($card['current_total'], 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between gap-5">
                                    <span class="text-slate-500">Vencimento</span>
                                    <span class="font-semibold text-blue-400">{{ $card['due_date']->format('d/m/Y') }}</span>
                                </div>
                                @if($card['last_bill'])
                                <div class="flex justify-between gap-5">
                                    <span class="text-slate-500">Última fatura</span>
                                    <span class="font-semibold {{ $card['last_bill']->isPaid() ? 'text-emerald-400' : 'text-orange-400' }}">
                                        {{ $card['last_bill']->isPaid() ? '✓ Paga' : 'Pendente' }}
                                        &middot; R$ {{ number_format($card['last_bill']->total_amount, 2, ',', '.') }}
                                    </span>
                                </div>
                                @endif
                            </div>
                            <svg class="w-5 h-5 text-slate-600 group-hover:text-blue-400 group-hover:translate-x-1 transition-all shrink-0"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                {{-- Card 2: Fatura atual com lançamentos (mostrar/recolher) --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden"
                     x-data="{ open: false }">

                    {{-- Header clicável --}}
                    <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between px-6 py-5 hover:bg-slate-800/50 transition-colors">
                        <div class="text-left">
                            <p class="text-base font-semibold text-slate-200">Fatura atual</p>
                            <p class="text-sm text-slate-500 mt-0.5">
                                {{ $card['period_start']->format('d/m') }} a {{ $card['period_end']->format('d/m/Y') }}
                                &middot;
                                <span class="{{ $daysToClose <= 3 ? 'text-red-400 font-semibold' : 'text-slate-500' }}">
                                    @if($daysToClose >= 0)
                                        fecha em {{ $daysToClose }} dia(s)
                                    @else
                                        período encerrado
                                    @endif
                                </span>
                                &middot; vence {{ $card['due_date']->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-2xl font-bold text-blue-400">
                                R$ {{ number_format($card['current_total'], 2, ',', '.') }}
                            </span>
                            <svg class="w-5 h-5 text-slate-500 transition-transform duration-200"
                                 :class="open ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    {{-- Lançamentos (recolhível) --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1">
                        @if($card['recent_transactions']->isEmpty())
                            <p class="text-slate-600 text-sm py-8 text-center border-t border-slate-800">
                                Nenhum lançamento neste período.
                            </p>
                        @else
                            <div class="border-t border-slate-800 divide-y divide-slate-800/50">
                                @foreach($card['recent_transactions'] as $t)
                                <div class="flex items-center gap-4 px-6 py-3 hover:bg-slate-800/30 transition-colors">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                         style="background-color:{{ ($t->category?->color ?? '#6B7280') }}20">
                                        <div class="w-2 h-2 rounded-full"
                                             style="background-color:{{ $t->category?->color ?? '#6B7280' }}"></div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-slate-300 truncate">{{ $t->description }}</p>
                                        <p class="text-xs text-slate-600">{{ $t->category?->name ?? '—' }} &middot; {{ $t->date->format('d/m') }}</p>
                                    </div>
                                    <span class="text-sm font-semibold text-red-400 shrink-0">
                                        -R$ {{ number_format($t->amount, 2, ',', '.') }}
                                    </span>
                                </div>
                                @endforeach
                            </div>

                            <div class="px-6 py-3 border-t border-slate-800 flex justify-between items-center bg-slate-800/20">
                                <span class="text-xs text-slate-500">{{ $card['recent_transactions']->count() }} lançamento(s)</span>
                                <div class="flex items-center gap-4">
                                    <a href="{{ route('transactions.create', ['account_id' => $account->id, 'type' => 'expense']) }}"
                                        class="text-xs text-slate-400 hover:text-emerald-400 transition-colors">
                                        + Lançamento
                                    </a>
                                    <a href="{{ route('credit-card-bills.statement', $account) }}"
                                        class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
                                        Ver extrato →
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
