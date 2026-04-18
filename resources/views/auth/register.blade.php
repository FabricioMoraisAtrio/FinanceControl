<x-guest-layout>

    <h2 class="text-base font-bold text-white mb-0.5">Criar conta</h2>
    <p class="text-xs text-slate-500 mb-5">Preencha os dados para começar</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- Nome --}}
        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Nome</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                placeholder="Seu nome completo"
                class="w-full bg-slate-800 border {{ $errors->has('name') ? 'border-red-500' : 'border-slate-700' }}
                       text-slate-100 rounded-xl px-3 py-2 text-sm placeholder-slate-600
                       focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
            @error('name')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- E-mail --}}
        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1.5">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
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
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Senha</label>
            <input type="password" name="password" required autocomplete="new-password"
                placeholder="Mínimo 8 caracteres"
                class="w-full bg-slate-800 border {{ $errors->has('password') ? 'border-red-500' : 'border-slate-700' }}
                       text-slate-100 rounded-xl px-3 py-2 text-sm placeholder-slate-600
                       focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
            @error('password')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirmar senha --}}
        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Confirmar senha</label>
            <input type="password" name="password_confirmation" required autocomplete="new-password"
                placeholder="Repita a senha"
                class="w-full bg-slate-800 border border-slate-700 text-slate-100 rounded-xl px-3 py-2 text-sm
                       placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
        </div>

        {{-- Botão --}}
        <button type="submit"
            class="w-full bg-emerald-500 hover:bg-emerald-400 text-white font-bold py-2.5 rounded-xl transition-colors text-sm mt-2">
            Criar conta
        </button>

        <p class="text-center text-sm text-slate-500">
            Já tem conta?
            <a href="{{ route('login') }}" class="text-emerald-400 hover:text-emerald-300 font-semibold transition-colors">
                Entrar
            </a>
        </p>
    </form>

</x-guest-layout>
