<x-app-layout>
    <x-slot name="title">Novo Lançamento — FinanceControl</x-slot>

    <div class="mb-5">
        <a href="{{ route('transactions.index') }}" class="text-sm text-slate-500 hover:text-slate-300 transition-colors">← Voltar</a>
        <h1 class="text-xl font-bold text-white mt-1">Novo Lançamento</h1>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-lg">
        <form action="{{ route('transactions.store') }}" method="POST" class="space-y-5" id="form">
            @csrf

            {{-- Tipo --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">Tipo</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['income' => '↑ Entrada', 'expense' => '↓ Saída', 'transfer' => '⇄ Transferência'] as $val => $label)
                    <label class="cursor-pointer text-center">
                        <input type="radio" name="type" value="{{ $val }}" {{ old('type', 'expense') === $val ? 'checked' : '' }}
                            class="sr-only peer" onchange="onTypeChange(this.value)">
                        <span class="block border border-slate-700 bg-slate-800 rounded-xl py-3 text-sm font-semibold text-slate-400
                            peer-checked:border-2
                            {{ $val === 'income'   ? 'peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-400' : '' }}
                            {{ $val === 'expense'  ? 'peer-checked:border-red-500 peer-checked:bg-red-500/10 peer-checked:text-red-400' : '' }}
                            {{ $val === 'transfer' ? 'peer-checked:border-blue-500 peer-checked:bg-blue-500/10 peer-checked:text-blue-400' : '' }}
                            hover:border-slate-600 hover:text-slate-200 transition-all">
                            {{ $label }}
                        </span>
                    </label>
                    @endforeach
                </div>
                @error('type')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Descrição --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Descrição</label>
                <input type="text" name="description" value="{{ old('description') }}" required
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 placeholder-slate-600"
                    placeholder="Ex: Aluguel, Salário, Notebook...">
                @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Valor e Data --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">
                        Valor Total (R$)
                    </label>
                    <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required
                        class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 placeholder-slate-600"
                        placeholder="0,00" oninput="calcInstallment()">
                    @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Data</label>
                    <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                        class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Conta --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Conta</label>
                <select name="account_id" required id="account_id"
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Selecione a conta...</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}"
                            data-type="{{ $account->type }}"
                            {{ old('account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->name }}
                            @if($account->type === 'credit_card') 💳 @endif
                            — R$ {{ number_format($account->balance, 2, ',', '.') }}
                        </option>
                    @endforeach
                </select>
                @error('account_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Conta Destino (transferência) --}}
            <div id="field-account-to" class="{{ old('type', 'expense') === 'transfer' ? '' : 'hidden' }}">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Conta Destino</label>
                <select name="account_to_id"
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Selecione...</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('account_to_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->name }}
                        </option>
                    @endforeach
                </select>
                @error('account_to_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Categoria (oculto para transferência) --}}
            <div id="field-category" class="{{ old('type', 'expense') === 'transfer' ? 'hidden' : '' }}">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Categoria</label>
                <select name="category_id"
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Sem categoria</option>
                    <optgroup label="Despesas">
                        @foreach($categories->where('type', 'expense') as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Receitas">
                        @foreach($categories->where('type', 'income') as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>

            {{-- Parcelamento (apenas para despesa) --}}
            <div id="field-installment" class="{{ old('type', 'expense') === 'expense' ? '' : 'hidden' }}">
                <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Parcelamento</label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="toggle-installment" class="sr-only peer"
                                {{ old('installment_total', 1) > 1 ? 'checked' : '' }}
                                onchange="toggleInstallment(this.checked)">
                            <div class="relative w-10 h-5 bg-slate-700 rounded-full peer-checked:bg-emerald-500 transition-colors">
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5 duration-200"></div>
                            </div>
                            <span class="text-xs text-slate-400 peer-checked:text-emerald-400" id="toggle-label">À vista</span>
                        </label>
                    </div>

                    <div id="installment-fields" class="{{ old('installment_total', 1) > 1 ? '' : 'hidden' }} space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Nº de Parcelas</label>
                                <select name="installment_total" id="installment_total"
                                    class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    onchange="calcInstallment()">
                                    @foreach([2,3,4,5,6,7,8,9,10,11,12,18,24,36,48,60] as $n)
                                        <option value="{{ $n }}" {{ old('installment_total') == $n ? 'selected' : '' }}>{{ $n }}x</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Valor por Parcela</label>
                                <div id="installment-value"
                                    class="bg-slate-700/50 border border-slate-600 rounded-lg px-3 py-2 text-sm text-emerald-400 font-bold">
                                    —
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">
                            As parcelas serão lançadas automaticamente nos meses seguintes.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Recorrência (fixo/variável) — oculto para transferência e parcelado --}}
            <div id="field-fixed" class="{{ old('type', 'expense') === 'transfer' ? 'hidden' : '' }}">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">Recorrência</label>
                <div class="flex gap-2">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="is_fixed" value="0" {{ old('is_fixed', '0') === '0' ? 'checked' : '' }} class="sr-only peer">
                        <span class="block border border-slate-700 bg-slate-800 rounded-xl py-2.5 text-sm text-center font-semibold text-slate-400
                            peer-checked:border-orange-500 peer-checked:bg-orange-500/10 peer-checked:text-orange-400
                            hover:border-slate-600 hover:text-slate-200 transition-all cursor-pointer">
                            Variável
                        </span>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="is_fixed" value="1" {{ old('is_fixed') === '1' ? 'checked' : '' }} class="sr-only peer">
                        <span class="block border border-slate-700 bg-slate-800 rounded-xl py-2.5 text-sm text-center font-semibold text-slate-400
                            peer-checked:border-purple-500 peer-checked:bg-purple-500/10 peer-checked:text-purple-400
                            hover:border-slate-600 hover:text-slate-200 transition-all cursor-pointer">
                            Fixa (recorrente)
                        </span>
                    </label>
                </div>
            </div>

            {{-- Observações --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">
                    Observações <span class="font-normal normal-case text-slate-600">(opcional)</span>
                </label>
                <textarea name="notes" rows="2"
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 placeholder-slate-600"
                    placeholder="Algum detalhe adicional...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="submit"
                    class="flex-1 bg-emerald-500 hover:bg-emerald-400 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-emerald-900/30 text-sm">
                    Registrar Lançamento
                </button>
                <a href="{{ route('transactions.index') }}"
                    class="px-5 py-3 text-sm font-semibold text-slate-400 border border-slate-700 rounded-xl hover:bg-slate-800 hover:text-slate-200 transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
    function onTypeChange(type) {
        const isTransfer    = type === 'transfer';
        const isExpense     = type === 'expense';

        document.getElementById('field-account-to').classList.toggle('hidden', !isTransfer);
        document.getElementById('field-category').classList.toggle('hidden', isTransfer);
        document.getElementById('field-fixed').classList.toggle('hidden', isTransfer);
        document.getElementById('field-installment').classList.toggle('hidden', !isExpense);

        // Ao sair de "despesa", zera parcelamento para não enviar valor acidentalmente
        if (!isExpense) {
            const toggle = document.getElementById('toggle-installment');
            if (toggle.checked) {
                toggle.checked = false;
                toggleInstallment(false);
            }
            document.getElementById('installment_total').value = '';
        }
    }

    function toggleInstallment(checked) {
        document.getElementById('installment-fields').classList.toggle('hidden', !checked);
        document.getElementById('toggle-label').textContent = checked ? 'Parcelado' : 'À vista';
        if (!checked) {
            document.getElementById('installment_total').value = '';
        }
        calcInstallment();
    }

    function calcInstallment() {
        const amountInput = document.querySelector('[name="amount"]');
        const totalInput  = document.getElementById('installment_total');
        const display     = document.getElementById('installment-value');
        const amount      = parseFloat(amountInput?.value || 0);
        const total       = parseInt(totalInput?.value || 1);

        if (amount > 0 && total > 1) {
            const per = (amount / total).toFixed(2).replace('.', ',');
            display.textContent = 'R$ ' + per;
        } else {
            display.textContent = '—';
        }
    }

    // Toggle switch animado
    document.getElementById('toggle-installment').addEventListener('change', function() {
        this.parentElement.querySelector('.absolute').style.transform =
            this.checked ? 'translateX(20px)' : 'translateX(0)';
    });

    // Ao carregar: se o toggle estiver desmarcado, garante que installment_total está vazio
    (function init() {
        const toggle = document.getElementById('toggle-installment');
        if (!toggle.checked) {
            document.getElementById('installment_total').value = '';
        }
    })();

    // No submit: limpa installment_total se o toggle não estiver ativo
    document.getElementById('form').addEventListener('submit', function() {
        const toggle = document.getElementById('toggle-installment');
        if (!toggle.checked) {
            document.getElementById('installment_total').value = '';
        }
    });
</script>
</x-app-layout>
