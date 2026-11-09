<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upgrade to a Tiered Programme') }}
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
                            <h3 class="text-xl sm:text-2xl font-extrabold text-white">{{ __('Upgrade to a Tiered Programme') }}</h3>
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
                            <p class="text-sm text-gray-500">{{ __('Current Programme') }}</p>
                            <p class="text-base font-medium text-gray-900">{{ $enrollment->course->name }} ({{ $enrollment->course->duration_weeks }} {{ __('Weeks') }}) — ₦{{ number_format($enrollment->fee(), 2) }}</p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600">
                        {{ __('Weekend, Executive, and VIP programmes can be upgraded into at any time, not just within the first five training days. The student pays only the difference between their current programme fee and the new one, and their training progress is not reset - days already attended carry over toward the new programme.') }}
                    </p>

                    @unless (auth()->user()->isDirector())
                        <p class="text-sm text-gold-700 bg-gold-50 ring-1 ring-gold-200 rounded-xl p-3">
                            {{ __('You are not a Director, so submitting this will raise a pending request - the programme only changes once a Director approves it.') }}
                        </p>
                    @endunless

                    <x-input-error :messages="$errors->get('enrollment')" />

                    <form method="post" action="{{ route('enrollments.upgrade-tier.store', $enrollment) }}" class="space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="course_id" :value="__('New Programme')" />
                            <x-select id="course_id" name="course_id" class="mt-1 block w-full" required>
                                <option value="">{{ __('Select') }}</option>
                                @foreach ($eligibleCourses as $course)
                                    <option value="{{ $course->id }}" @selected((string) old('course_id') === (string) $course->id)>[{{ $course->tierLabel() }}] {{ $course->name }} ({{ $course->duration_weeks }} {{ __('Weeks') }}) — ₦{{ number_format($course->fee, 2) }}</option>
                                @endforeach
                            </x-select>
                            <x-input-error class="mt-2" :messages="$errors->get('course_id')" />
                        </div>

                        <div class="bg-navy text-gold-400 rounded-xl p-4 inline-flex items-baseline gap-2">
                            <span class="text-sm font-medium">{{ __('Upgrade Balance') }}</span>
                            <span class="text-2xl font-bold" id="preview-upgrade-balance">₦0.00</span>
                        </div>

                        <div>
                            <x-input-label for="amount_paid" :value="__('Amount to Collect Now')" />
                            <x-text-input id="amount_paid" name="amount_paid" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('amount_paid')" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Defaults to the full upgrade balance above; lower it to collect only part of it now.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('amount_paid')" />
                        </div>

                        <div>
                            <x-input-label for="payment_method" :value="__('Payment Method')" />
                            <x-select id="payment_method" name="payment_method" class="mt-1 block w-full">
                                <option value="">{{ __('Select') }}</option>
                                @foreach (['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ __($label) }}</option>
                                @endforeach
                            </x-select>
                            <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-line">
                            <x-primary-button type="submit">{{ auth()->user()->isDirector() ? __('Upgrade Programme') : __('Request Upgrade') }}</x-primary-button>
                            <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                        </div>
                    </form>

                    <script>
                        (function () {
                            var courseFees = @json($eligibleCourses->pluck('fee', 'id'));
                            var currentFee = {{ $enrollment->fee() }};
                            var discountAmount = {{ (float) ($enrollment->discount_amount ?? 0) }};
                            var courseSelect = document.getElementById('course_id');
                            var amountPaidInput = document.getElementById('amount_paid');
                            var previewBalance = document.getElementById('preview-upgrade-balance');

                            function formatNaira(amount) {
                                return '₦' + amount.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }

                            function upgradeBalance() {
                                var courseFee = parseFloat(courseFees[courseSelect.value] || 0);
                                var newFee = Math.max(0, courseFee - discountAmount);

                                return Math.max(0, newFee - currentFee);
                            }

                            function recalculate() {
                                var balance = upgradeBalance();

                                previewBalance.textContent = formatNaira(balance);
                                amountPaidInput.max = balance;

                                if (courseSelect.value && (amountPaidInput.value === '' || parseFloat(amountPaidInput.value) > balance)) {
                                    amountPaidInput.value = balance.toFixed(2);
                                }
                            }

                            courseSelect.addEventListener('change', recalculate);
                            recalculate();
                        })();
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
