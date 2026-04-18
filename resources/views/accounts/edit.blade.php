<x-app-layout>
    <x-slot name="title">Editar Conta — FinanceControl</x-slot>

    <div class="mb-5">
        <a href="{{ route('accounts.index') }}" class="text-sm text-slate-500 hover:text-slate-300 transition-colors">← Voltar</a>
        <h1 class="text-xl font-bold text-white mt-1">Editar Conta</h1>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-lg">
        <form action="{{ route('accounts.update', $account) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Nome</label>
                <input type="text" name="name" value="{{ old('name', $account->name) }}" required
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Tipo</label>
                <select name="type" required
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="checking"    {{ old('type', $account->type) === 'checking'    ? 'selected' : '' }}>Conta Corrente</option>
                    <option value="savings"     {{ old('type', $account->type) === 'savings'     ? 'selected' : '' }}>Poupança</option>
                    <option value="cash"        {{ old('type', $account->type) === 'cash'        ? 'selected' : '' }}>Dinheiro</option>
                    <option value="investment"  {{ old('type', $account->type) === 'investment'  ? 'selected' : '' }}>Investimento</option>
                    <option value="credit_card" {{ old('type', $account->type) === 'credit_card' ? 'selected' : '' }}>Cartão de Crédito</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Cor</label>
                <input type="color" name="color" value="{{ old('color', $account->color) }}"
                    class="h-10 w-full bg-slate-800 border border-slate-700 rounded-xl px-2 py-1 cursor-pointer">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Saldo Inicial (R$)</label>
                <input type="number" name="initial_balance" id="initial_balance"
                    value="{{ old('initial_balance', $account->initial_balance) }}" step="0.01" required
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p class="text-xs text-slate-600 mt-1">Saldo que a conta tinha antes do primeiro lançamento registrado.</p>
                @error('initial_balance')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            @if($account->type === 'credit_card')
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Limite do cartão (R$)</label>
                <input type="number" name="credit_limit" step="0.01" min="0"
                    value="{{ old('credit_limit', $account->credit_limit) }}"
                    placeholder="Ex: 5000.00"
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('credit_limit')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @endif

            @if($account->type !== 'credit_card')
            {{-- Reconciliação: ajusta o saldo inicial para bater com o valor real --}}
            <div class="bg-blue-500/5 border border-blue-500/20 rounded-xl p-4">
                <p class="text-xs font-semibold text-blue-400 uppercase tracking-widest mb-3">Reconciliar Saldo</p>
                <p class="text-xs text-slate-400 mb-3">
                    Saldo atual calculado:
                    <span class="font-bold {{ $account->balance >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        R$ {{ number_format($account->balance, 2, ',', '.') }}
                    </span>
                    &mdash; se não bater com o extrato real, informe o valor correto abaixo e clique em <strong>Usar este valor</strong>.
                </p>
                <div class="flex gap-2">
                    <input type="number" id="real_balance" step="0.01" placeholder="Ex: 1.250,00"
                        class="flex-1 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" onclick="reconcile()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl transition-colors">
                        Usar este valor
                    </button>
                </div>
                <p class="text-xs text-slate-600 mt-2" id="reconcile-info"></p>
            </div>
            @endif

            <div class="flex items-center gap-3">
                <input type="checkbox" name="active" id="active" value="1"
                    {{ old('active', $account->active) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-emerald-500 focus:ring-emerald-500">
                <label for="active" class="text-sm font-medium text-slate-300">Conta ativa</label>
            </div>

            <input type="hidden" name="icon" value="{{ $account->icon }}">

            <div class="flex gap-3 pt-1">
                <button type="submit"
                    class="flex-1 bg-emerald-500 hover:bg-emerald-400 text-white font-bold py-3 rounded-xl transition-colors text-sm">
                    Salvar Alterações
                </button>
                <a href="{{ route('accounts.index') }}"
                    class="px-5 py-3 text-sm font-semibold text-slate-400 border border-slate-700 rounded-xl hover:bg-slate-800 hover:text-slate-200 transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
<script>
    function reconcile() {
        const realBalance = parseFloat(document.getElementById('real_balance').value);
        if (isNaN(realBalance)) return;

        const currentComputed = {{ $account->balance }};
        const currentInitial  = {{ $account->initial_balance ?? 0 }};
        const newInitial      = currentInitial + (realBalance - currentComputed);

        document.getElementById('initial_balance').value = newInitial.toFixed(2);
        document.getElementById('reconcile-info').textContent =
            'Saldo inicial ajustado para R$ ' + newInitial.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) +
            '. Clique em Salvar Alterações para confirmar.';
    }
</script>
</x-app-layout>
