@php
    $buildingIconPath = 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21';
    $mapPinIconPath = 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z';
    $userIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
    $phoneIconPath = 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z';
    $envelopeIconPath = 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75';
@endphp

<div>
    <x-input-label for="name" :value="__('Company Name')" />
    <div class="relative mt-1">
        <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center rounded-l-lg bg-amber-50 text-amber-500">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $buildingIconPath }}" /></svg>
        </span>
        <x-text-input id="name" name="name" type="text" class="block w-full pl-14" placeholder="{{ __('e.g. Arco Worldwide') }}" :value="old('name', $company->name ?? '')" required autofocus />
    </div>
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="address" :value="__('Address')" />
        <div class="relative mt-1">
            <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center rounded-l-lg bg-amber-50 text-amber-500">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $mapPinIconPath }}" /></svg>
            </span>
            <x-text-input id="address" name="address" type="text" class="block w-full pl-14" placeholder="{{ __('Street address') }}" :value="old('address', $company->address ?? '')" />
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('address')" />
    </div>

    <div>
        <x-input-label for="city" :value="__('City')" />
        <div class="relative mt-1">
            <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center rounded-l-lg bg-amber-50 text-amber-500">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $mapPinIconPath }}" /></svg>
            </span>
            <x-text-input id="city" name="city" type="text" class="block w-full pl-14" placeholder="{{ __('e.g. Port Harcourt') }}" :value="old('city', $company->city ?? '')" />
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('city')" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="contact_person" :value="__('Contact Person')" />
        <div class="relative mt-1">
            <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center rounded-l-lg bg-amber-50 text-amber-500">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $userIconPath }}" /></svg>
            </span>
            <x-text-input id="contact_person" name="contact_person" type="text" class="block w-full pl-14" placeholder="{{ __('e.g. Mr. John') }}" :value="old('contact_person', $company->contact_person ?? '')" />
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
    </div>

    <div>
        <x-input-label for="phone" :value="__('Phone Number')" />
        <div class="relative mt-1">
            <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center rounded-l-lg bg-amber-50 text-amber-500">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $phoneIconPath }}" /></svg>
            </span>
            <x-text-input id="phone" name="phone" type="text" class="block w-full pl-14" placeholder="{{ __('e.g. 08012345678') }}" :value="old('phone', $company->phone ?? '')" />
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
    </div>
</div>

<div>
    <x-input-label for="email" :value="__('Email Address')" />
    <div class="relative mt-1">
        <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center rounded-l-lg bg-amber-50 text-amber-500">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $envelopeIconPath }}" /></svg>
        </span>
        <x-text-input id="email" name="email" type="email" class="block w-full pl-14" placeholder="{{ __('company@email.com') }}" :value="old('email', $company->email ?? '')" />
    </div>
    <x-input-error class="mt-2" :messages="$errors->get('email')" />
</div>

<div>
    <x-input-label for="notes" :value="__('Notes (optional)')" />
    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" placeholder="{{ __('Anything else worth remembering about this client') }}">{{ old('notes', $company->notes ?? '') }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
</div>
