@props(['options' => [], 'value' => null, 'type' => 'text'])

<div
    x-data="{
        open: false,
        query: {{ Illuminate\Support\Js::from((string) $value) }},
        options: {{ Illuminate\Support\Js::from(array_values($options)) }},
        get filtered() {
            return this.query === ''
                ? this.options
                : this.options.filter((option) => option.toLowerCase().includes(this.query.toLowerCase()));
        },
        select(option) {
            this.query = option;
            this.open = false;
        },
    }"
    x-on:click.outside="open = false"
    class="relative"
>
    <input
        type="{{ $type }}"
        x-model="query"
        x-on:focus="open = true"
        x-on:click="open = true"
        x-on:input="open = true"
        autocomplete="off"
        {{ $attributes->merge(['class' => 'border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm block w-full pr-9']) }}
    >
    @if (count($options) > 0)
        <button type="button" x-on:click="open = !open" tabindex="-1" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
        </button>
        <ul x-show="open && filtered.length > 0" x-cloak class="absolute z-10 mt-1 max-h-48 w-full overflow-auto rounded-md bg-white py-1 text-sm shadow-lg ring-1 ring-gray-200">
            <template x-for="option in filtered" :key="option">
                <li x-on:click="select(option)" x-text="option" class="cursor-pointer px-3 py-2 text-gray-700 hover:bg-amber-50"></li>
            </template>
        </ul>
    @endif
</div>
