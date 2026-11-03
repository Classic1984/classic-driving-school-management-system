<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-1.5 px-4 py-2.5 bg-ink border border-transparent rounded-lg font-semibold text-xs text-gold-400 uppercase tracking-wider shadow-sm hover:bg-ink-700 hover:shadow-md focus:bg-ink-800 active:bg-ink focus:outline-none focus:ring-2 focus:ring-gold-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
