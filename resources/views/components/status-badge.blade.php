@props(['status'])

@php
    $styles = [
        'pending' => 'bg-amber-100 text-amber-800',
        'approved' => 'bg-emerald-100 text-emerald-800',
        'rejected' => 'bg-rose-100 text-rose-800',
        'expired' => 'bg-slate-200 text-slate-600',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold '.($styles[$status] ?? 'bg-slate-100 text-slate-600')]) }}>
    {{ ucfirst($status) }}
</span>
