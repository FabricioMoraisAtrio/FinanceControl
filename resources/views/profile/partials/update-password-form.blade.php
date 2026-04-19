<section>
    <h2 class="text-base font-semibold text-white mb-0.5">Alterar Senha</h2>
    <p class="text-xs text-slate-500 mb-5">Use uma senha longa e aleatória para manter sua conta segura.</p>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Senha Atual</label>
            <input id="update_password_current_password" name="current_password" type="password"
                class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Nova Senha</label>
            <input id="update_password_password" name="password" type="password"
                class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                autocomplete="new-password">
            @error('password', 'updatePassword')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Confirmar Senha</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit"
                class="bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                Atualizar Senha
            </button>
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-emerald-400">Senha atualizada.</p>
            @endif
        </div>
    </form>
</section>
