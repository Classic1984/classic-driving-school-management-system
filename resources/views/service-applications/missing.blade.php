{{--
    Shown instead of a raw 404 when the catalog Service this section is
    built on doesn't exist yet by its expected exact name - see
    ServiceApplicationController::missingServiceView().
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                </span>

                <h3 class="mt-4 text-lg font-bold text-gray-900">{{ __(':title isn\'t set up yet', ['title' => $title]) }}</h3>
                <p class="mt-2 text-sm text-gray-500">
                    {{ __('This section needs a catalog service named exactly ":name". Once it\'s added under Services, this page will work.', ['name' => $serviceName]) }}
                </p>

                @if (auth()->user()->isDirector())
                    <a href="{{ route('services.create', ['name' => $serviceName, 'price' => $suggested['price'] ?? null, 'processing_days' => $suggested['processing_days'] ?? null]) }}" class="mt-6 inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-black transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Add ":name"', ['name' => $serviceName]) }}
                    </a>
                    <p class="mt-2 text-xs text-gray-400">{{ __('The name, price, and processing days are pre-filled - just double-check and save.') }}</p>
                @else
                    <p class="mt-4 text-sm text-gray-500">{{ __('Ask a director to add it under Services.') }}</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
