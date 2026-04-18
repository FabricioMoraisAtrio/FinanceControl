@props(['active' => false])

<a {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all ' . ($active
    ? 'bg-emerald-500/15 text-emerald-400 font-semibold ring-1 ring-emerald-500/20'
    : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100')]) }}>
    {{ $slot }}
</a>
