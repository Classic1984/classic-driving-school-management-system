<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Staff') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                <p class="text-sm text-gray-500 mb-6">{{ __('Share the password you set below with the new staff member directly - they can change it afterward from the login page\'s "Forgot your password?" link.') }}</p>

                <form method="post" action="{{ route('users.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-input-label for="role" :value="__('Role')" />
                        <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                            <option value="">{{ __('Select a role') }}</option>
                            @foreach (['admin' => 'Admin (view, no deletes, no Finance)', 'secretary' => 'Secretary (day-to-day operations)', 'director' => 'Director (full access)'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('role') === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('role')" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Create Staff Account') }}</x-primary-button>
                        <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
