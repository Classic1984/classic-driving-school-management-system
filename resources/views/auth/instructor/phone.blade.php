<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 text-center">
        {{ __('Instructor Login') }}
    </div>

    <x-input-error class="mb-4" :messages="$errors->get('phone')" />

    <form method="post" action="{{ route('instructor.login.send-code') }}">
        @csrf

        <div>
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" type="tel" name="phone" :value="old('phone')" class="mt-1 block w-full" required autofocus autocomplete="tel" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>{{ __('Continue') }}</x-primary-button>
        </div>
    </form>

    <p class="mt-4 text-center text-xs text-gray-400">
        {{ __('Staff member? Use the') }} <a href="{{ route('login') }}" class="text-amber-600 hover:underline">{{ __('regular login') }}</a>.
    </p>
</x-guest-layout>
