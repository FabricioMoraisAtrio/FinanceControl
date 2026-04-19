<section class="space-y-4">
    <h2 class="text-base font-semibold text-red-400">Excluir Conta</h2>
    <p class="text-xs text-slate-500">
        Ao excluir sua conta, todos os dados serão removidos permanentemente. Esta ação não pode ser desfeita.
    </p>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center gap-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-400 text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
        Excluir minha conta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-slate-900">
            @csrf
            @method('delete')

            <h2 class="text-base font-semibold text-white">Confirmar exclusão da conta</h2>
            <p class="mt-1 text-sm text-slate-500 mb-5">
                Todos os seus dados serão removidos permanentemente. Digite sua senha para confirmar.
            </p>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Senha</label>
                <input id="password" name="password" type="password" placeholder="Sua senha"
                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                @error('password', 'userDeletion')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-5 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')"
                    class="px-4 py-2 text-sm text-slate-400 hover:text-white border border-slate-700 hover:bg-slate-800 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-500 rounded-xl transition-colors">
                    Excluir conta
                </button>
            </div>
        </form>
    </x-modal>
</section>
