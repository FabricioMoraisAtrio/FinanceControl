<x-app-layout>
    <x-slot name="title">Nova Conta — FinanceControl</x-slot>

    <div class="mb-5">
        <a href="{{ route('accounts.index') }}" class="text-sm text-slate-500 hover:text-slate-300 transition-colors">← Voltar</a>
        <h1 class="text-xl font-bold text-white mt-1">Nova Conta</h1>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-lg"
         x-data="{ type: '{{ old('type', 'checking') }}' }">
        <form action="{{ route('accounts.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 placeholder-slate-600"
                    placeholder="Ex: Nubank, Carteira, Poupança...">
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Tipo</label>
                <select name="type" required x-model="type"
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="checking" {{ old('type') === 'checking' ? 'selected' : '' }}>Conta Corrente</option>
                    <option value="savings"  {{ old('type') === 'savings'  ? 'selected' : '' }}>Poupança</option>
                    <option value="cash"     {{ old('type') === 'cash'     ? 'selected' : '' }}>Dinheiro</option>
                    <option value="investment"   {{ old('type') === 'investment'   ? 'selected' : '' }}>Investimento</option>
                    <option value="credit_card"  {{ old('type') === 'credit_card'  ? 'selected' : '' }}>Cartão de Crédito</option>
                </select>
            </div>

            {{-- Saldo Inicial (contas comuns) --}}
            <div x-show="type !== 'credit_card'">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Saldo Inicial (R$)</label>
                <input type="number" name="initial_balance" value="{{ old('initial_balance', '0') }}" step="0.01"
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p class="text-xs text-slate-600 mt-1">Saldo que a conta já tinha antes de usar o sistema.</p>
                @error('initial_balance')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Campos exclusivos do cartão de crédito --}}
            <div x-show="type === 'credit_card'" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Limite do Cartão (R$)</label>
                    <input type="number" name="credit_limit" value="{{ old('credit_limit', '0') }}" step="0.01" min="0"
                        class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('credit_limit')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Dia de Fechamento</label>
                        <input type="number" name="closing_day" value="{{ old('closing_day', '21') }}" min="1" max="28"
                            class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('closing_day')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Dia de Vencimento</label>
                        <input type="number" name="payment_day" value="{{ old('payment_day', '10') }}" min="1" max="28"
                            class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('payment_day')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <p class="text-xs text-slate-600 -mt-1">O vencimento é calculado no mês seguinte ao fechamento.</p>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Débito automático em</label>
                    <select name="payment_account_id"
                        class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Não usar débito automático</option>
                        @foreach($paymentAccounts as $pa)
                            <option value="{{ $pa->id }}" {{ old('payment_account_id') == $pa->id ? 'selected' : '' }}>
                                {{ $pa->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-600 mt-1">Conta debitada automaticamente no vencimento da fatura.</p>
                    @error('payment_account_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Cor de identificação</label>
                <input type="color" name="color" value="{{ old('color', '#10B981') }}"
                    class="h-10 w-full bg-slate-800 border border-slate-700 rounded-xl px-2 py-1 cursor-pointer">
            </div>

            <input type="hidden" name="icon" value="bank">

            <div class="flex gap-3 pt-1">
                <button type="submit"
                    class="flex-1 bg-emerald-500 hover:bg-emerald-400 text-white font-bold py-3 rounded-xl transition-colors text-sm">
                    Salvar Conta
                </button>
                <a href="{{ route('accounts.index') }}"
                    class="px-5 py-3 text-sm font-semibold text-slate-400 border border-slate-700 rounded-xl hover:bg-slate-800 hover:text-slate-200 transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
