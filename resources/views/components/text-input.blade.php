@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm transition duration-150 ease-in-out disabled:bg-gray-50 disabled:text-gray-500']) }}>
