<x-guest-layout>
    <div class="text-center">
        <p class="text-lg font-semibold text-gray-800">{{ __('Welcome, :name', ['name' => $student->name]) }}</p>
        <p class="mt-2 text-sm text-gray-500">
            {{ __("You're logged in. Your training progress, payments, and certificate status are coming soon.") }}
        </p>

        <form method="post" action="{{ route('student.logout') }}" class="mt-6">
            @csrf
            <x-secondary-button type="submit">{{ __('Log Out') }}</x-secondary-button>
        </form>
    </div>
</x-guest-layout>
