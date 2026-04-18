<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'FinanceControl') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 text-sm">

        <div class="w-full" style="max-width:380px">

            {{-- Logo / Marca --}}
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-10 h-10 bg-emerald-500/15 rounded-xl mb-3">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="text-lg font-bold text-white">FinanceControl</h1>
                <p class="text-xs text-slate-500 mt-0.5">Controle financeiro pessoal</p>
            </div>

            {{-- Card --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl">
                {{ $slot }}
            </div>

        </div>
    </body>
</html>
