<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enroll :name', ['name' => $driver->name]) }}
        </h2>
    </x-slot>

    @php
        $userIconPath = ['M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z'];
    @endphp

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-gradient-to-r from-navy-900 via-navy-800 to-navy px-4 py-6 sm:px-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-gold-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75">
                        @foreach ($userIconPath as $path)
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                        @endforeach
                    </svg>

                    <div class="relative flex items-center gap-4 min-w-0">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gold-500/15 text-gold-400 ring-1 ring-gold-400/30">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                @foreach ($userIconPath as $path)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                                @endforeach
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-xl sm:text-2xl font-extrabold text-white truncate">{{ __('Enroll :name', ['name' => $driver->name]) }}</h3>
                            <p class="text-sm text-gray-400 truncate">{{ __('Sponsored by :company - no payment needed here, their training fee is covered by the companys invoice.', ['company' => $driver->company->name]) }}</p>
                        </div>
                    </div>
                </div>

                <form method="post" action="{{ route('corporate-company-drivers.enroll-store', $driver) }}" class="p-4 sm:p-8 space-y-6">
                    @csrf

                    <div>
                        <x-input-label :value="__('Driver Name')" />
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $driver->name }}</p>
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('Phone')" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $driver->phone)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                    </div>

                    <div>
                        <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                        <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
                    </div>

                    <div>
                        <x-input-label for="course_id" :value="__('Course')" />
                        <x-select id="course_id" name="course_id" class="mt-1 block w-full" required>
                            <option value="">{{ __('Select a course') }}</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error class="mt-2" :messages="$errors->get('course_id')" />
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t border-line">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-navy hover:bg-navy-700 px-5 py-3 text-sm font-bold text-gold-400 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                            {{ __('Enroll as Student') }}
                        </button>
                        <a href="{{ route('corporate-companies.show', $driver->company) }}#drivers" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-line hover:bg-paper px-5 py-3 text-sm font-semibold text-gray-700 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
