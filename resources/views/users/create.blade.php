<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Staff') }}
        </h2>
    </x-slot>

    @php
        $usersIconPath = 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $envelopeIconPath = 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75';
        $shieldCheckIconPath = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
        $lockIconPath = 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z';
        $eyeIconPath = 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z';
        $eyeSlashIconPath = 'M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88';
        $infoIconPath = 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z';
    @endphp

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Add Staff') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Create a new staff account') }}</p>
                </div>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                </span>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 space-y-6">
                <div class="flex items-start gap-3 rounded-lg bg-amber-50 ring-1 ring-amber-100 p-4">
                    <svg class="h-5 w-5 shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $infoIconPath }}" /></svg>
                    <p class="text-sm text-amber-800">{{ __('Share the password you set below with the new staff member directly - they can change it afterward from the login page\'s "Forgot your password?" link.') }}</p>
                </div>

                <form method="post" action="{{ route('users.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center text-amber-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                            </span>
                            <x-text-input id="name" name="name" type="text" class="block w-full pl-11" :value="old('name')" required autofocus />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $envelopeIconPath }}" /></svg>
                            </span>
                            <x-text-input id="email" name="email" type="email" class="block w-full pl-11" :value="old('email')" required />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-input-label for="role" :value="__('Role')" />
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shieldCheckIconPath }}" /></svg>
                            </span>
                            <select id="role" name="role" class="block w-full pl-11 border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm" required>
                                <option value="">{{ __('Select a role') }}</option>
                                @foreach (['admin' => 'Admin (view, no deletes, no Finance)', 'secretary' => 'Secretary (day-to-day operations)', 'director' => 'Director (full access)'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role') === $value)>{{ __($label) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('role')" />
                    </div>

                    <div x-data="{ show: false }">
                        <x-input-label for="password" :value="__('Password')" />
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $lockIconPath }}" /></svg>
                            </span>
                            <x-text-input id="password" name="password" type="password" x-bind:type="show ? 'text' : 'password'" class="block w-full pl-11 pr-11" required autocomplete="new-password" />
                            <button type="button" x-on:click="show = !show" class="absolute right-0 top-0 flex h-full w-11 items-center justify-center text-gray-400 hover:text-gray-600">
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $eyeIconPath }}" /></svg>
                                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $eyeSlashIconPath }}" /></svg>
                            </button>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>

                    <div x-data="{ show: false }">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $lockIconPath }}" /></svg>
                            </span>
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" x-bind:type="show ? 'text' : 'password'" class="block w-full pl-11 pr-11" required autocomplete="new-password" />
                            <button type="button" x-on:click="show = !show" class="absolute right-0 top-0 flex h-full w-11 items-center justify-center text-gray-400 hover:text-gray-600">
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $eyeIconPath }}" /></svg>
                                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $eyeSlashIconPath }}" /></svg>
                            </button>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-5 py-3 text-sm font-bold text-amber-400 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                            {{ __('Create Staff Account') }}
                        </button>
                        <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
