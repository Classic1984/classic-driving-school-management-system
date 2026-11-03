@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 rounded-lg mx-0 text-start text-base font-semibold text-accent-700 bg-accent-100 focus:outline-none focus:text-accent-700 focus:bg-accent-100 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 rounded-lg mx-0 text-start text-base font-medium text-stone-600 hover:text-ink hover:bg-accent-50 focus:outline-none focus:text-ink focus:bg-accent-50 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
