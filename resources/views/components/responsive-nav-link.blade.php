@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full px-4 py-2 rounded-lg text-start text-sm font-semibold text-gold-200 bg-gold-500/15 focus:outline-none focus:text-gold-200 focus:bg-gold-500/15 transition duration-150 ease-in-out'
            : 'block w-full px-4 py-2 rounded-lg text-start text-sm font-medium text-navy-200 hover:text-white hover:bg-white/5 focus:outline-none focus:text-white focus:bg-white/5 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
