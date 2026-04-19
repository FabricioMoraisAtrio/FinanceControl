<x-app-layout>
    <x-slot name="title">Meu Perfil — FinanceControl</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Meu Perfil</h1>
        <p class="text-sm text-slate-500 mt-0.5">Gerencie suas informações de conta</p>
    </div>

    <div class="space-y-5 max-w-xl">

        {{-- Informações --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Senha --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            @include('profile.partials.update-password-form')
        </div>

        {{-- Excluir conta --}}
        <div class="bg-slate-900 border border-red-500/20 rounded-2xl p-6">
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</x-app-layout>
