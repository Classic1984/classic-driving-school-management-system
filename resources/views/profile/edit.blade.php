<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    @php
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-navy-900 via-navy-800 to-navy px-4 py-6 sm:px-8">
                <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-gold-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" />
                </svg>

                <div class="relative flex items-center gap-4 min-w-0">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gold-500/15 text-gold-400 ring-1 ring-gold-400/30">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-xl sm:text-2xl font-extrabold text-white truncate">{{ __('Account Settings') }}</h3>
                        <p class="text-sm text-gray-400 truncate">{{ __("Update your account's profile information and email address.") }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-6 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-6 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                <div class="max-w-xl">
                    @include('profile.partials.two-factor-authentication-form')
                </div>
            </div>

            <div class="p-4 sm:p-6 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
