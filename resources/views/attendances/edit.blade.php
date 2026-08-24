<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Training Login') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                <form method="post" action="{{ route('attendances.update', $attendance) }}" class="space-y-6">
                    @csrf
                    @method('put')
                    @if ($redirectTo)
                        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                    @endif

                    @include('attendances.partials.form-fields')

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ match ($redirectTo) {
                            'student' => route('students.show', $attendance->student_id),
                            'training_record' => route('students.training-record', $attendance->student_id),
                            default => route('attendances.index'),
                        } }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
