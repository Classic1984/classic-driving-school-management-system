@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'border-line focus:border-gold-500 focus:ring-gold-500 rounded-lg shadow-sm transition duration-150 ease-in-out disabled:bg-paper disabled:text-gray-500']) }}>
    {{ $slot }}
</select>
