<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 text-center">
        {{ __('First time logging in - enter the code we texted you and choose a PIN you\'ll use going forward.') }}
    </div>

    <x-input-error class="mb-4" :messages="$errors->get('otp')" />
    <x-input-error class="mb-4" :messages="$errors->get('pin')" />

    <form method="post" action="{{ route('student.verify-otp.store') }}">
        @csrf

        <div>
            <x-input-label for="otp" :value="__('Verification Code')" />
            <x-text-input id="otp" type="text" inputmode="numeric" name="otp" class="mt-1 block w-full" required autofocus autocomplete="one-time-code" />
        </div>

        <div class="mt-4">
            <x-input-label for="pin" :value="__('Choose a PIN (4-6 digits)')" />
            <x-text-input id="pin" type="password" inputmode="numeric" name="pin" class="mt-1 block w-full" required autocomplete="new-password" />
        </div>

        <div class="mt-4">
            <x-input-label for="pin_confirmation" :value="__('Confirm PIN')" />
            <x-text-input id="pin_confirmation" type="password" inputmode="numeric" name="pin_confirmation" class="mt-1 block w-full" required autocomplete="new-password" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>{{ __('Set PIN & Log In') }}</x-primary-button>
        </div>
    </form>

    <p class="mt-4 text-center text-xs text-gray-400">
        <a href="{{ route('student.login') }}" class="text-amber-600 hover:underline">{{ __('Use a different phone number') }}</a>
    </p>
</x-guest-layout>
