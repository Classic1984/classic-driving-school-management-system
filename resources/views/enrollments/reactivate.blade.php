<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reactivate Enrollment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-6">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Student') }}</p>
                    <p class="text-base font-medium text-gray-900">{{ $enrollment->student->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">{{ __('Course') }}</p>
                    <p class="text-base font-medium text-gray-900">{{ $enrollment->course->name }}</p>
                </div>

                <div class="bg-black text-amber-400 rounded-lg p-4 inline-flex items-baseline gap-2">
                    <span class="text-sm font-medium">{{ __('Outstanding Balance') }}</span>
                    <span class="text-2xl font-bold">₦{{ number_format($enrollment->balance(), 2) }}</span>
                </div>

                <p class="text-sm text-gray-600">
                    {{ __('This enrollment is locked because its two-month training period expired. Reactivating it will collect the outstanding balance above plus the additional fee agreed with the student, as a single payment, and give the student a fresh two-month training period starting today. Their training and attendance history are unaffected.') }}
                </p>

                <x-input-error :messages="$errors->get('enrollment')" />

                <form method="post" action="{{ route('enrollments.reactivate', $enrollment) }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="additional_fee" :value="__('Additional Reactivation Fee')" />
                        <x-text-input id="additional_fee" name="additional_fee" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('additional_fee')" required autofocus />
                        <p class="mt-1 text-xs text-gray-500">{{ __('The fee agreed with the student to resume training, on top of the outstanding balance above.') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('additional_fee')" />
                    </div>

                    <div>
                        <x-input-label for="payment_method" :value="__('Payment Method')" />
                        <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                            @foreach (['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                    </div>

                    <div>
                        <x-input-label for="reference_number" :value="__('Reference Number')" />
                        <x-text-input id="reference_number" name="reference_number" type="text" class="mt-1 block w-full" :value="old('reference_number')" />
                        <x-input-error class="mt-2" :messages="$errors->get('reference_number')" />
                    </div>

                    <div>
                        <x-input-label for="notes" :value="__('Notes')" />
                        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button type="submit">{{ __('Reactivate Enrollment') }}</x-primary-button>
                        <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
