<x-app-layout>
    <x-slot name="title">Editar Lançamento — FinanceControl</x-slot>

    <div class="mb-5">
        <a href="{{ route('transactions.index') }}" class="text-sm text-slate-500 hover:text-slate-300 transition-colors">← Voltar</a>
        <h1 class="text-xl font-bold text-white mt-1">Editar Lançamento</h1>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-lg">

        {{-- Info somente leitura --}}
        <div class="flex gap-4 p-4 bg-slate-800 rounded-xl mb-5 border border-slate-700">
            <div>
                <p class="text-xs text-slate-500">Tipo</p>
                <p class="font-semibold text-slate-200 text-sm">{{ $transaction->getTypeLabel() }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Valor</p>
                <p class="font-bold text-white text-sm">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Conta</p>
                <p class="font-semibold text-slate-200 text-sm">{{ $transaction->account->name }}</p>
            </div>
        </div>
        <p class="text-xs text-slate-600 mb-5">Para alterar tipo, valor ou conta, remova e crie novamente.</p>

        <form action="{{ route('transactions.update', $transaction) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Descrição</label>
                <input type="text" name="description" value="{{ old('description', $transaction->description) }}" required
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Data</label>
                <input type="date" name="date" value="{{ old('date', $transaction->date->format('Y-m-d')) }}" required
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Categoria</label>
                <select name="category_id"
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Sem categoria</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $transaction->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($transaction->type !== 'transfer')
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">Recorrência</label>
                <div class="flex gap-2">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="is_fixed" value="0" {{ !old('is_fixed', $transaction->is_fixed) ? 'checked' : '' }} class="sr-only peer">
                        <span class="block border border-slate-700 bg-slate-800 rounded-xl py-2.5 text-sm text-center font-semibold text-slate-400
                            peer-checked:border-orange-500 peer-checked:bg-orange-500/10 peer-checked:text-orange-400
                            hover:border-slate-600 hover:text-slate-200 transition-all cursor-pointer">
                            Variável
                        </span>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="is_fixed" value="1" {{ old('is_fixed', $transaction->is_fixed) ? 'checked' : '' }} class="sr-only peer">
                        <span class="block border border-slate-700 bg-slate-800 rounded-xl py-2.5 text-sm text-center font-semibold text-slate-400
                            peer-checked:border-purple-500 peer-checked:bg-purple-500/10 peer-checked:text-purple-400
                            hover:border-slate-600 hover:text-slate-200 transition-all cursor-pointer">
                            Fixa (recorrente)
                        </span>
                    </label>
                </div>
            </div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Observações</label>
                <textarea name="notes" rows="2"
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 placeholder-slate-600">{{ old('notes', $transaction->notes) }}</textarea>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="submit"
                    class="flex-1 bg-emerald-500 hover:bg-emerald-400 text-white font-bold py-3 rounded-xl transition-colors text-sm">
                    Salvar
                </button>
                <a href="{{ route('transactions.index') }}"
                    class="px-5 py-3 text-sm font-semibold text-slate-400 border border-slate-700 rounded-xl hover:bg-slate-800 hover:text-slate-200 transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
