<x-app-layout>
    <x-slot name="title">Categorias — FinanceControl</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Categorias</h1>
        <a href="{{ route('categories.create') }}"
            class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-emerald-900/30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Categoria
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach([
            'expense' => ['label' => 'Saídas / Despesas', 'dot' => 'bg-red-500'],
            'income'  => ['label' => 'Entradas / Receitas', 'dot' => 'bg-emerald-500'],
        ] as $type => $meta)
        <div>
            <div class="flex items-center gap-2 mb-3">
                <div class="w-2 h-2 rounded-full {{ $meta['dot'] }}"></div>
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-widest">{{ $meta['label'] }}</h2>
            </div>

            @php $filtered = $categories->where('type', $type); @endphp

            @if($filtered->isEmpty())
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center">
                    <p class="text-slate-600 text-sm">Nenhuma categoria cadastrada.</p>
                    <a href="{{ route('categories.create') }}" class="text-xs text-emerald-400 hover:text-emerald-300 mt-2 inline-block">
                        + Adicionar categoria
                    </a>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($filtered as $category)
                    <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 flex items-center gap-3 hover:border-slate-700 transition-colors group">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background-color: {{ $category->color }}25">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $category->color }}"></div>
                        </div>
                        <span class="flex-1 font-medium text-slate-200 text-sm">{{ $category->name }}</span>

                        {{-- Todos os usuários podem editar/excluir suas próprias categorias --}}
                        <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('categories.edit', $category) }}"
                                class="text-xs text-slate-400 hover:text-white border border-slate-700 rounded-lg px-2.5 py-1 hover:bg-slate-700 transition-colors">
                                Editar
                            </a>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                onsubmit="return confirm('Remover a categoria \'{{ $category->name }}\'? Lançamentos vinculados ficarão sem categoria.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="text-xs text-red-400 hover:text-red-300 border border-red-500/20 rounded-lg px-2.5 py-1 hover:bg-red-500/10 transition-colors">
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endforeach
    </div>
</x-app-layout>
