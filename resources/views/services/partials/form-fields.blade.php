@php
    $tagIconPath = ['M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.169.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z', 'M6 6h.008v.008H6V6Z'];
    $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
    $infoIconPath = 'm11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z';
@endphp

<div>
    <x-input-label for="name" :value="__('Service Name')" />
    <div class="relative mt-1">
        <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center rounded-l-lg bg-amber-50 text-amber-500">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                @foreach ($tagIconPath as $path)
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                @endforeach
            </svg>
        </span>
        <x-text-input id="name" name="name" type="text" class="block w-full pl-14" placeholder="{{ __('Enter service name') }}" :value="old('name', $service->name ?? '')" required autofocus />
    </div>
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div>
    <x-input-label for="price" :value="__('Price (₦)')" />
    <div class="relative mt-1">
        <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center rounded-l-lg bg-amber-50 text-amber-500 font-bold">
            ₦
        </span>
        <x-text-input id="price" name="price" type="number" step="0.01" min="0.01" class="block w-full pl-14" placeholder="{{ __('Enter price') }}" :value="old('price', $service->price ?? '')" required />
    </div>
    <x-input-error class="mt-2" :messages="$errors->get('price')" />
</div>

<div>
    <x-input-label for="processing_days" :value="__('Processing Days (optional)')" />
    <div class="relative mt-1">
        <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center rounded-l-lg bg-amber-50 text-amber-500">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
        </span>
        <x-text-input id="processing_days" name="processing_days" type="number" step="1" min="1" class="block w-full pl-14" placeholder="{{ __('Enter processing days (e.g. 30)') }}" :value="old('processing_days', $service->processing_days ?? '')" />
    </div>
    <x-input-error class="mt-2" :messages="$errors->get('processing_days')" />

    <div class="mt-3 flex items-start gap-3 rounded-lg bg-blue-50 ring-1 ring-blue-100 p-4">
        <svg class="h-5 w-5 shrink-0 text-blue-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $infoIconPath }}" /></svg>
        <p class="text-sm text-blue-800">{{ __('If this service has a typical turnaround (e.g. 30 days for a Driver\'s License), staff marking it "Processing" will show its progress on the dashboard. Leave blank if there\'s nothing meaningful to track.') }}</p>
    </div>
</div>

<div>
    <div class="flex items-center gap-2.5">
        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $service->is_active ?? true)) class="h-5 w-5 rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
        <label for="is_active" class="text-sm font-medium text-gray-800">{{ __('Active (billable to students)') }}</label>
    </div>
    <p class="mt-1 ml-[1.9rem] text-sm text-gray-500">{{ __('Active services will be available to add on student payments.') }}</p>
    <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
</div>
