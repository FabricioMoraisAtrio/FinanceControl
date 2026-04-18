<x-app-layout>
    <x-slot name="title">Nova Categoria — FinanceControl</x-slot>

    <div class="mb-5">
        <a href="{{ route('categories.index') }}" class="text-sm text-slate-500 hover:text-slate-300 transition-colors">← Voltar</a>
        <h1 class="text-xl font-bold text-white mt-1">Nova Categoria</h1>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-lg">
        <form action="{{ route('categories.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 placeholder-slate-600"
                    placeholder="Ex: Alimentação, Salário...">
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">Tipo</label>
                <div class="flex gap-2">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="type" value="expense" {{ old('type', 'expense') === 'expense' ? 'checked' : '' }} class="sr-only peer">
                        <span class="block border border-slate-700 bg-slate-800 rounded-xl py-3 text-sm text-center font-semibold text-slate-400
                            peer-checked:border-red-500 peer-checked:bg-red-500/10 peer-checked:text-red-400
                            hover:border-slate-600 hover:text-slate-200 transition-all">
                            ↓ Saída / Despesa
                        </span>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="type" value="income" {{ old('type') === 'income' ? 'checked' : '' }} class="sr-only peer">
                        <span class="block border border-slate-700 bg-slate-800 rounded-xl py-3 text-sm text-center font-semibold text-slate-400
                            peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-400
                            hover:border-slate-600 hover:text-slate-200 transition-all">
                            ↑ Entrada / Receita
                        </span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Cor</label>
                <input type="color" name="color" value="{{ old('color', '#6B7280') }}"
                    class="h-10 w-full bg-slate-800 border border-slate-700 rounded-xl px-2 py-1 cursor-pointer">
            </div>

            <input type="hidden" name="icon" value="tag">

            <div class="flex gap-3 pt-1">
                <button type="submit"
                    class="flex-1 bg-emerald-500 hover:bg-emerald-400 text-white font-bold py-3 rounded-xl transition-colors text-sm">
                    Salvar Categoria
                </button>
                <a href="{{ route('categories.index') }}"
                    class="px-5 py-3 text-sm font-semibold text-slate-400 border border-slate-700 rounded-xl hover:bg-slate-800 hover:text-slate-200 transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
