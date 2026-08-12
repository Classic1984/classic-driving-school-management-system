<div>
    <x-input-label for="name" :value="__('Service Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $service->name ?? '')" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div>
    <x-input-label for="price" :value="__('Price')" />
    <x-text-input id="price" name="price" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('price', $service->price ?? '')" required />
    <x-input-error class="mt-2" :messages="$errors->get('price')" />
</div>

<div>
    <x-input-label for="processing_days" :value="__('Processing Days (optional)')" />
    <x-text-input id="processing_days" name="processing_days" type="number" step="1" min="1" class="mt-1 block w-full" :value="old('processing_days', $service->processing_days ?? '')" />
    <p class="mt-1 text-sm text-gray-500">{{ __('If this service has a typical turnaround (e.g. 30 days for a Driver\'s License), staff marking it "Processing" will show its progress on the dashboard. Leave blank if there\'s nothing meaningful to track.') }}</p>
    <x-input-error class="mt-2" :messages="$errors->get('processing_days')" />
</div>

<div class="flex items-center">
    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $service->is_active ?? true)) class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
    <label for="is_active" class="ms-2 text-sm text-gray-600">{{ __('Active (billable to students)') }}</label>
    <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
</div>
