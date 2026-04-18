<x-guest-layout>

    <h2 class="text-base font-bold text-white mb-0.5">Bem-vindo de volta</h2>
    <p class="text-xs text-slate-500 mb-5">Acesse sua conta</p>

    <x-auth-session-status class="mb-4 text-sm text-emerald-400" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- E-mail --}}
        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1.5">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                placeholder="seu@email.com"
                class="w-full bg-slate-800 border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-700' }}
                       text-slate-100 rounded-xl px-3 py-2 text-sm placeholder-slate-600
                       focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
            @error('email')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Senha --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-widest">Senha</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">
                        Esqueceu a senha?
                    </a>
                @endif
            </div>
            <input type="password" name="password" required autocomplete="current-password"
                placeholder="Sua senha"
                class="w-full bg-slate-800 border {{ $errors->has('password') ? 'border-red-500' : 'border-slate-700' }}
                       text-slate-100 rounded-xl px-3 py-2 text-sm placeholder-slate-600
                       focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
            @error('password')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Lembrar --}}
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="remember"
                class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-emerald-500 focus:ring-emerald-500">
            <span class="text-sm text-slate-400">Lembrar de mim</span>
        </label>

        {{-- Botão --}}
        <button type="submit"
            class="w-full bg-emerald-500 hover:bg-emerald-400 text-white font-bold py-2.5 rounded-xl transition-colors text-sm">
            Entrar
        </button>

        <p class="text-center text-sm text-slate-500">
            Não tem conta?
            <a href="{{ route('register') }}" class="text-emerald-400 hover:text-emerald-300 font-semibold transition-colors">
                Criar conta
            </a>
        </p>
    </form>

</x-guest-layout>
