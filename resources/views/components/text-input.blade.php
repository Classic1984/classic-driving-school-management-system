@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-line focus:border-accent-500 focus:ring-accent-500 rounded-lg shadow-sm transition duration-150 ease-in-out disabled:bg-stone-50 disabled:text-stone-500']) }}>
