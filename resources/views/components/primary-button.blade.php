<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-1.5 px-4 py-2.5 bg-accent-500 border border-transparent rounded-xl font-semibold text-sm text-white shadow-sm hover:bg-accent-600 hover:shadow-md focus:bg-accent-600 active:bg-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-400 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
