<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reactivate Enrollment') }}
        </h2>
    </x-slot>

    @php
        $academicCapIconPath = 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5';
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-gradient-to-r from-navy-900 via-navy-800 to-navy px-4 py-6 sm:px-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-gold-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapIconPath }}" /></svg>

                    <div class="relative flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gold-500/15 text-gold-400 ring-1 ring-gold-400/30">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapIconPath }}" /></svg>
                        </span>
                        <div>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-white">{{ __('Reactivate Enrollment') }}</h3>
                            <p class="text-sm text-gray-400">{{ $enrollment->student->name }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-8 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Student') }}</p>
                            <p class="text-base font-medium text-gray-900">{{ $enrollment->student->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Course') }}</p>
                            <p class="text-base font-medium text-gray-900">{{ $enrollment->course->name }}</p>
                        </div>
                    </div>

                    <div class="bg-navy text-gold-400 rounded-xl p-4 inline-flex items-baseline gap-2">
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
                            <x-select id="payment_method" name="payment_method" class="mt-1 block w-full" required>
                                @foreach (['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ __($label) }}</option>
                                @endforeach
                            </x-select>
                            <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                        </div>

                        <div>
                            <x-input-label for="reference_number" :value="__('Reference Number')" />
                            <x-text-input id="reference_number" name="reference_number" type="text" class="mt-1 block w-full" :value="old('reference_number')" />
                            <x-input-error class="mt-2" :messages="$errors->get('reference_number')" />
                        </div>

                        <div>
                            <x-input-label for="notes" :value="__('Notes')" />
                            <x-textarea id="notes" name="notes" rows="2" class="mt-1 block w-full">{{ old('notes') }}</x-textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-line">
                            <x-primary-button type="submit">{{ __('Reactivate Enrollment') }}</x-primary-button>
                            <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
