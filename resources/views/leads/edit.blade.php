<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Inquiry') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-6">
                <form method="post" action="{{ route('leads.update', $lead) }}" class="space-y-6">
                    @csrf
                    @method('put')

                    @include('leads.partials.form-fields')

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('leads.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>

                <div class="pt-6 border-t border-gray-200">
                    <a href="{{ route('students.create', ['name' => $lead->name, 'phone' => $lead->phone]) }}" class="text-sm text-amber-600 hover:underline">
                        {{ __('Register This Lead as a Student →') }}
                    </a>
                    <p class="mt-1 text-xs text-gray-500">{{ __("Pre-fills the student registration form with this lead's name and phone. Remember to mark this inquiry Converted once registration is complete.") }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
