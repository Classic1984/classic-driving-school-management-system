<x-guest-layout>
    <div class="text-center">
        <p class="text-lg font-semibold text-gray-800">{{ __('Welcome, :name', ['name' => $instructor->name]) }}</p>
        <p class="mt-2 text-sm text-gray-500">
            {{ __("You're logged in. Your schedule, attendance marking, and assessment tools are coming soon.") }}
        </p>

        <form method="post" action="{{ route('instructor.logout') }}" class="mt-6">
            @csrf
            <x-secondary-button type="submit">{{ __('Log Out') }}</x-secondary-button>
        </form>
    </div>
</x-guest-layout>
