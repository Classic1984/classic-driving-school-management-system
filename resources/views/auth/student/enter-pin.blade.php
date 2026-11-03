<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 text-center">
        {{ __('Enter your PIN to log in.') }}
    </div>

    <x-input-error class="mb-4" :messages="$errors->get('pin')" />

    <form method="post" action="{{ route('student.enter-pin.store') }}">
        @csrf

        <div>
            <x-input-label for="pin" :value="__('PIN')" />
            <x-text-input id="pin" type="password" inputmode="numeric" name="pin" class="mt-1 block w-full" required autofocus autocomplete="current-password" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>{{ __('Log In') }}</x-primary-button>
        </div>
    </form>

    <p class="mt-4 text-center text-xs text-gray-400">
        <a href="{{ route('student.login') }}" class="text-amber-600 hover:underline">{{ __('Use a different phone number') }}</a>
    </p>
</x-guest-layout>
