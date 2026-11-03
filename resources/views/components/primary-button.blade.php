<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-1.5 px-4 py-2.5 bg-gold-500 border border-transparent rounded-xl font-bold text-sm text-navy-900 shadow-sm hover:bg-gold-400 hover:shadow-md focus:bg-gold-400 active:bg-gold-600 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
