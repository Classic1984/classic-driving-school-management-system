<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upgrade Programme') }}
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
                    <p class="text-sm text-gray-500">{{ __('Current Programme') }}</p>
                    <p class="text-base font-medium text-gray-900">{{ $enrollment->course->name }} ({{ $enrollment->course->duration_weeks }} {{ __('Weeks') }}) — ₦{{ number_format($enrollment->fee(), 2) }}</p>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
                    {{ __('Training Days Completed') }}: {{ $enrollment->attendedDays() }} &middot;
                    {{ __('Upgrade Window') }}: {{ $enrollment->upgradeDaysRemaining() }} {{ __('day(s) remaining') }}
                </div>

                <p class="text-sm text-gray-600">
                    {{ __('The student pays only the difference between their current programme fee and the new one. Their training progress is not reset - days already attended carry over toward the new programme.') }}
                </p>

                <x-input-error :messages="$errors->get('enrollment')" />

                <form method="post" action="{{ route('enrollments.upgrade.store', $enrollment) }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="course_id" :value="__('New Programme')" />
                        <select id="course_id" name="course_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                            <option value="">{{ __('Select') }}</option>
                            @foreach ($eligibleCourses as $course)
                                <option value="{{ $course->id }}" @selected((string) old('course_id') === (string) $course->id)>{{ $course->name }} ({{ $course->duration_weeks }} {{ __('Weeks') }}) — ₦{{ number_format($course->fee, 2) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('course_id')" />
                    </div>

                    <div class="bg-black text-amber-400 rounded-lg p-4 inline-flex items-baseline gap-2">
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
                        <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                            <option value="">{{ __('Select') }}</option>
                            @foreach (['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button type="submit">{{ __('Upgrade Programme') }}</x-primary-button>
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
</x-app-layout>
