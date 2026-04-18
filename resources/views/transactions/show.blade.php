<x-app-layout>
    <x-slot name="title">Transação — FinanceControl</x-slot>

    <div class="mb-6">
        <a href="{{ route('transactions.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Voltar</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">Detalhe da Transação</h1>
    </div>

    <div class="max-w-lg bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
            <div class="w-12 h-12 rounded-full flex items-center justify-center
                {{ $transaction->type === 'income' ? 'bg-emerald-100' : ($transaction->type === 'expense' ? 'bg-red-100' : 'bg-blue-100') }}">
                <span class="text-xl font-bold {{ $transaction->type === 'income' ? 'text-emerald-600' : ($transaction->type === 'expense' ? 'text-red-500' : 'text-blue-500') }}">
                    {{ $transaction->type === 'expense' ? '-' : '+' }}
                </span>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-lg">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</p>
                <p class="text-sm text-gray-500">{{ $transaction->getTypeLabel() }}</p>
            </div>
        </div>

        <dl class="space-y-3">
            <div class="flex justify-between">
                <dt class="text-sm text-gray-500">Descrição</dt>
                <dd class="text-sm font-medium text-gray-800">{{ $transaction->description }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-sm text-gray-500">Data</dt>
                <dd class="text-sm font-medium text-gray-800">{{ $transaction->date->format('d/m/Y') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-sm text-gray-500">Conta</dt>
                <dd class="text-sm font-medium text-gray-800">{{ $transaction->account->name }}</dd>
            </div>
            @if($transaction->category)
            <div class="flex justify-between">
                <dt class="text-sm text-gray-500">Categoria</dt>
                <dd class="text-sm font-medium text-gray-800">{{ $transaction->category->name }}</dd>
            </div>
            @endif
            @if($transaction->notes)
            <div>
                <dt class="text-sm text-gray-500 mb-1">Observações</dt>
                <dd class="text-sm text-gray-800 bg-gray-50 rounded-lg p-3">{{ $transaction->notes }}</dd>
            </div>
            @endif
        </dl>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('transactions.edit', $transaction) }}" class="flex-1 text-center bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 rounded-lg transition-colors text-sm">Editar</a>
            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" onsubmit="return confirm('Remover?')" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-medium py-2 rounded-lg transition-colors text-sm">Remover</button>
            </form>
        </div>
    </div>
</x-app-layout>
