<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Request a Correction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                <p class="text-sm text-gray-600 mb-6">
                    {{ __('This information can only be changed by a Director. Describe what needs to change and why - a Director will review it and make the correction.') }}
                </p>

                <form method="post" action="{{ route('student-correction-requests.store', $student) }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="field" :value="__('Field')" />
                        <select id="field" name="field" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                            @foreach (['name' => 'Name', 'date_of_birth' => 'Date of Birth', 'phone' => 'Phone', 'program' => 'Training Program'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('field', $field) === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('field')" />
                    </div>

                    <div>
                        <x-input-label :value="__('Current Value')" />
                        <p class="mt-1 text-sm text-gray-900">
                            {{ match ($field) {
                                'name' => $student->name,
                                'date_of_birth' => optional($student->date_of_birth)->format('Y-m-d') ?? '—',
                                'phone' => $student->phone,
                                'program' => $student->courses->pluck('name')->implode(', ') ?: '—',
                                default => '—',
                            } }}
                        </p>
                    </div>

                    <div>
                        <x-input-label for="requested_value" :value="__('Requested Value')" />
                        <x-text-input id="requested_value" name="requested_value" type="text" class="mt-1 block w-full" :value="old('requested_value')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('requested_value')" />
                    </div>

                    <div>
                        <x-input-label for="reason" :value="__('Reason (optional)')" />
                        <textarea id="reason" name="reason" rows="3" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">{{ old('reason') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Submit Request') }}</x-primary-button>
                        <a href="{{ route('students.show', $student) }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
