<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border border-line rounded-xl font-semibold text-sm text-ink shadow-sm hover:bg-accent-50 hover:border-accent-200 focus:outline-none focus:ring-2 focus:ring-accent-400 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
