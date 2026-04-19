<section>
    <h2 class="text-base font-semibold text-white mb-0.5">Informações do Perfil</h2>
    <p class="text-xs text-slate-500 mb-5">Atualize seu nome e e-mail de acesso.</p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">Nome</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus
                class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 placeholder-slate-600">
            @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1.5">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 placeholder-slate-600">
            @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit"
                class="bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                Salvar
            </button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-emerald-400">Salvo com sucesso.</p>
            @endif
        </div>
    </form>
</section>
