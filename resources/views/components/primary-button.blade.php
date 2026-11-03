<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-1.5 px-4 py-2.5 bg-black border border-transparent rounded-lg font-semibold text-xs text-amber-400 uppercase tracking-wider shadow-sm hover:bg-gray-900 hover:shadow-md focus:bg-gray-900 active:bg-black focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
