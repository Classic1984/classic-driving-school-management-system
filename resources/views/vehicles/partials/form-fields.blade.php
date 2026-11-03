@php($vehicle = $vehicle ?? null)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $vehicle?->name)" placeholder="{{ __('e.g. Toyota Corolla') }}" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="plate_number" :value="__('Plate Number')" />
        <x-text-input id="plate_number" name="plate_number" type="text" class="mt-1 block w-full" :value="old('plate_number', $vehicle?->plate_number)" required />
        <x-input-error class="mt-2" :messages="$errors->get('plate_number')" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
            @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $vehicle?->status ?? 'active') === $value)>{{ __($label) }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>
</div>
