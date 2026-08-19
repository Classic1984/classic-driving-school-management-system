<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-4">
                @if (session('status') === 'student-created')
                    <p class="text-sm font-medium text-green-600">{{ __('Student registered successfully.') }}</p>
                @elseif (session('status') === 'student-created-discount-pending')
                    <p class="text-sm font-medium text-green-600">{{ __('Student registered successfully.') }}</p>
                    <p class="text-sm font-medium text-amber-600">{{ __('The requested discount is pending Director approval - the student is enrolled at the full fee until then.') }}</p>
                    <script>alert({!! json_encode(__('Student registered successfully. The requested discount still needs Director approval before it applies - the student is currently enrolled at the full course fee.')) !!});</script>
                @elseif (session('status') === 'student-updated')
                    <p class="text-sm font-medium text-green-600">{{ __('Student updated successfully.') }}</p>
                @elseif (session('status') === 'payment-created')
                    <p class="text-sm font-medium text-green-600">{{ __('Payment recorded successfully.') }}</p>
                @elseif (session('status') === 'correction-requested')
                    <p class="text-sm font-medium text-green-600">{{ __('Correction request submitted. A Director will review it.') }}</p>
                @elseif (session('status') === 'service-charged')
                    <p class="text-sm font-medium text-green-600">{{ __('Service charge added successfully.') }}</p>
                @elseif (session('status') === 'service-status-updated')
                    <p class="text-sm font-medium text-green-600">{{ __('Processing status updated successfully.') }}</p>
                @endif

                @if ($student->photo_path)
                    <img src="{{ Storage::url($student->photo_path) }}" alt="{{ __('Passport photo') }}" class="h-24 w-24 object-cover rounded-md border border-gray-200">
                @endif

                <dl class="divide-y divide-gray-100">
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Student ID') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 font-mono">{{ $student->student_id_number }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->name }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Email') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->email }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Phone') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->phone }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Address') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->address ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Date of Birth') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->date_of_birth->format('Y-m-d') }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Mother Maiden Name') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->mother_maiden_name ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Sex') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 capitalize">{{ $student->sex ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('State of Origin') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->state_of_origin ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Local Govt. Area') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->local_government_area ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Occupation') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ match ($student->occupation) {
                            'student' => 'Student',
                            'business' => 'Business',
                            'other' => 'Others',
                            default => '—',
                        } }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('License Number') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->license_number ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Course Type') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 capitalize">{{ $student->course_type }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Vehicle Class') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 capitalize">{{ $student->vehicle_class ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Previous Driving Experience') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ is_null($student->has_driving_experience) ? '—' : ($student->has_driving_experience ? __('Yes') : __('No')) }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Wears Glasses to Drive') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ is_null($student->wears_glasses) ? '—' : ($student->wears_glasses ? __('Yes') : __('No')) }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('How They Heard About Us') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 capitalize">
                            {{ $student->referral_source ?? '—' }}
                            @if ($student->referral_source === 'other' && $student->referral_source_other)
                                ({{ $student->referral_source_other }})
                            @endif
                        </dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Enrollment Date') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->enrollment_date->format('Y-m-d') }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <x-badge :color="match ($student->status) {
                                'active' => 'green',
                                'completed' => 'blue',
                                'withdrawn' => 'red',
                                default => 'gray',
                            }" class="capitalize">{{ $student->status }}</x-badge>
                        </dd>
                    </div>
                </dl>

                @php
                    $totalCharges = $financialOverview->sum('price');
                    $totalOverviewPaid = $financialOverview->sum('paid');
                    $totalOutstanding = $financialOverview->sum('balance');
                @endphp
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Financial Overview') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div class="bg-black text-amber-400 rounded-lg p-4">
                            <p class="text-xs uppercase tracking-wider">{{ __('Total Charges') }}</p>
                            <p class="text-2xl font-bold mt-1">₦{{ number_format($totalCharges, 2) }}</p>
                        </div>
                        <div class="bg-black text-amber-400 rounded-lg p-4">
                            <p class="text-xs uppercase tracking-wider">{{ __('Total Paid') }}</p>
                            <p class="text-2xl font-bold mt-1">₦{{ number_format($totalOverviewPaid, 2) }}</p>
                        </div>
                        @if ($totalOutstanding > 0)
                            <a href="{{ route('payments.record.create', ['student_id' => $student->id]) }}" class="bg-amber-500 text-black rounded-lg p-4 hover:bg-amber-400">
                                <p class="text-xs uppercase tracking-wider">{{ __('Total Outstanding') }}</p>
                                <p class="text-2xl font-bold mt-1">₦{{ number_format($totalOutstanding, 2) }}</p>
                                <p class="text-xs mt-1 underline">{{ __('Click to record a payment') }}</p>
                            </a>
                        @else
                            <div class="bg-amber-500 text-black rounded-lg p-4">
                                <p class="text-xs uppercase tracking-wider">{{ __('Total Outstanding') }}</p>
                                <p class="text-2xl font-bold mt-1">₦{{ number_format($totalOutstanding, 2) }}</p>
                            </div>
                        @endif
                    </div>

                    @if ($financialOverview->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        <th class="px-2 py-1">{{ __('Item') }}</th>
                                        <th class="px-2 py-1">{{ __('Price') }}</th>
                                        <th class="px-2 py-1">{{ __('Paid') }}</th>
                                        <th class="px-2 py-1">{{ __('Balance') }}</th>
                                        <th class="px-2 py-1">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($financialOverview as $charge)
                                        <tr>
                                            <td class="px-2 py-1 text-sm">{{ $charge['label'] }}</td>
                                            <td class="px-2 py-1 text-sm">{{ number_format($charge['price'], 2) }}</td>
                                            <td class="px-2 py-1 text-sm">{{ number_format($charge['paid'], 2) }}</td>
                                            <td class="px-2 py-1 text-sm">
                                                @if ($charge['balance'] > 0)
                                                    <a href="{{ route('payments.record.create', ['student_id' => $student->id, 'charge_type' => $charge['type'], 'charge_id' => $charge['id']]) }}" class="text-amber-600 hover:underline">
                                                        {{ number_format($charge['balance'], 2) }}
                                                    </a>
                                                @else
                                                    {{ number_format($charge['balance'], 2) }}
                                                @endif
                                            </td>
                                            <td class="px-2 py-1 text-sm">
                                                <x-badge :color="match ($charge['status']) {
                                                    'paid' => 'green',
                                                    'part_payment' => 'amber',
                                                    default => 'red',
                                                }">{{ __(ucwords(str_replace('_', ' ', $charge['status']))) }}</x-badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="border-t-2 border-gray-300 font-semibold">
                                        <td class="px-2 py-1 text-sm">{{ __('TOTAL') }}</td>
                                        <td class="px-2 py-1 text-sm">{{ number_format($totalCharges, 2) }}</td>
                                        <td class="px-2 py-1 text-sm">{{ number_format($totalOverviewPaid, 2) }}</td>
                                        <td class="px-2 py-1 text-sm">{{ number_format($totalOutstanding, 2) }}</td>
                                        <td class="px-2 py-1 text-sm">{{ $totalOutstanding > 0 ? __('Outstanding') : __('Paid') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">{{ __('No charges yet.') }}</p>
                    @endif
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Next of Kin') }}</h3>
                    <dl class="divide-y divide-gray-100">
                        <div class="py-2 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $student->next_of_kin_name ?? '—' }}</dd>
                        </div>
                        <div class="py-2 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Address') }}</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $student->next_of_kin_address ?? '—' }}</dd>
                        </div>
                        <div class="py-2 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Phone No.') }}</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $student->next_of_kin_phone ?? '—' }}</dd>
                        </div>
                        <div class="py-2 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Email') }}</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $student->next_of_kin_email ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-medium text-gray-500">{{ __('Enrollments') }}</h3>
                        @if (auth()->user()->isDirector())
                            <a href="{{ route('students.enroll.create', $student) }}" class="text-xs text-amber-600 hover:underline">{{ __('+ Enroll in a Course') }}</a>
                        @else
                            <span class="text-xs text-gray-400">
                                {{ __('🔒 Director-controlled') }} ·
                                <a href="{{ route('student-correction-requests.create', ['student' => $student, 'field' => 'program']) }}" class="text-amber-600 hover:underline">{{ __('Request a Correction') }}</a>
                            </span>
                        @endif
                    </div>

                    @if (session('status') === 'enrollment-completed')
                        <p class="mb-2 text-sm font-medium text-green-600">{{ __('Course marked as completed.') }}</p>
                    @elseif (session('status') === 'enrollment-removed')
                        <p class="mb-2 text-sm font-medium text-green-600">{{ __('Enrollment removed.') }}</p>
                    @elseif (session('status') === 'enrollment-upgraded')
                        <p class="mb-2 text-sm font-medium text-green-600">{{ __('Programme upgraded successfully.') }}</p>
                    @endif
                    <x-input-error class="mb-2" :messages="$errors->get('enrollment')" />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-2 py-1">{{ __('Course') }}</th>
                                    <th class="px-2 py-1">{{ __('Fee') }}</th>
                                    <th class="px-2 py-1">{{ __('Discount') }}</th>
                                    <th class="px-2 py-1">{{ __('Balance') }}</th>
                                    <th class="px-2 py-1">{{ __('Due Date') }}</th>
                                    <th class="px-2 py-1">{{ __('Status') }}</th>
                                    <th class="px-2 py-1"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($student->courses as $enrolledCourse)
                                    <tr>
                                        <td class="px-2 py-1 text-sm">{{ $enrolledCourse->name }}</td>
                                        <td class="px-2 py-1 text-sm">
                                            @if ($enrolledCourse->pivot->hasDiscount())
                                                <span class="line-through text-gray-400">₦{{ number_format($enrolledCourse->pivot->originalFee(), 2) }}</span>
                                                <span class="font-medium">₦{{ number_format($enrolledCourse->pivot->fee(), 2) }}</span>
                                            @else
                                                ₦{{ number_format($enrolledCourse->pivot->fee(), 2) }}
                                            @endif
                                        </td>
                                        <td class="px-2 py-1 text-sm">
                                            @if ($enrolledCourse->pivot->hasDiscount())
                                                <span class="text-green-600">{{ rtrim(rtrim(number_format((float) $enrolledCourse->pivot->discount_percentage, 2), '0'), '.') }}% (₦{{ number_format($enrolledCourse->pivot->discount_amount, 2) }})</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-2 py-1 text-sm">{{ number_format($enrolledCourse->pivot->balance(), 2) }}</td>
                                        <td class="px-2 py-1 text-sm">{{ optional($enrolledCourse->pivot->due_date)->format('Y-m-d') ?? '—' }}</td>
                                        <td class="px-2 py-1 text-sm">
                                            <x-badge :color="match ($enrolledCourse->pivot->statusLabel()) {
                                                'Registered' => 'gray',
                                                'Locked' => 'red',
                                                'Completed' => 'blue',
                                                'Certified' => 'amber',
                                                default => 'green',
                                            }">{{ __($enrolledCourse->pivot->statusLabel()) }}</x-badge>
                                            @if ($enrolledCourse->pivot->status === 'locked')
                                                <span class="block text-xs text-gray-500 mt-0.5">{{ $enrolledCourse->pivot->lockedReasonLabel() }}</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-1 text-sm">
                                            @if ($enrolledCourse->pivot->status !== 'completed' && $enrolledCourse->pivot->hasCompletedTraining() && $enrolledCourse->pivot->balance() <= 0)
                                                <form method="post" action="{{ route('enrollments.complete', $enrolledCourse->pivot->id) }}" class="inline">
                                                    @csrf
                                                    @method('patch')
                                                    <button type="submit" class="text-sm text-amber-600 hover:underline">{{ __('Mark Complete') }}</button>
                                                </form>
                                            @endif
                                            @if ($enrolledCourse->pivot->isLockedForExpiredTrainingPeriod() && auth()->user()->isDirector())
                                                <a href="{{ route('enrollments.reactivate.create', $enrolledCourse->pivot->id) }}" class="text-sm text-amber-600 hover:underline">{{ __('Reactivate') }}</a>
                                            @endif
                                            @if ($enrolledCourse->pivot->canUpgrade() && auth()->user()->isDirector())
                                                <a href="{{ route('enrollments.upgrade.create', $enrolledCourse->pivot->id) }}" class="text-sm text-amber-600 hover:underline">{{ __('Upgrade') }}</a>
                                            @endif
                                            @if (auth()->user()->isDirector() && $enrolledCourse->pivot->amountPaid() <= 0)
                                                <form method="post" action="{{ route('enrollments.destroy', $enrolledCourse->pivot->id) }}" class="inline" onsubmit="return confirm('{{ __('Remove this enrollment? This cannot be undone.') }}');">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Remove') }}</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-2 py-2 text-sm text-gray-500">{{ __('Not enrolled in any courses yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($student->courses->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Training Progress') }}</h3>
                        <div class="space-y-4">
                            @foreach ($student->courses as $enrolledCourse)
                                @php($label = $enrolledCourse->pivot->trainingStatusLabel())
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-semibold text-gray-800">{{ $enrolledCourse->name }} — {{ $enrolledCourse->duration_weeks }} {{ __('Weeks') }}</h4>
                                        <x-badge :color="match ($label) {
                                            'Completed' => 'blue',
                                            'Expired' => 'red',
                                            default => 'green',
                                        }">{{ __($label) }}</x-badge>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-amber-500 h-2.5 rounded-full" style="width: {{ $enrolledCourse->pivot->trainingCompletionPercentage() }}%"></div>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600">
                                        <span>{{ $enrolledCourse->pivot->attendedDays() }} / {{ $enrolledCourse->totalTrainingDays() }} {{ __('Days Completed') }}</span>
                                        <span>{{ $enrolledCourse->pivot->remainingTrainingDays() }} {{ __('Days Remaining') }}</span>
                                        <span>{{ $enrolledCourse->pivot->trainingCompletionPercentage() }}%</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ __('Start Date') }}: {{ optional($enrolledCourse->pivot->enrolled_at)->format('Y-m-d') ?? '—' }}
                                        &middot;
                                        {{ __('Expected Completion') }}: {{ optional($enrolledCourse->pivot->expectedCompletionDate())->format('Y-m-d') ?? '—' }}
                                    </div>
                                    @php($upgradeStatus = $enrolledCourse->pivot->upgradeStatusLabel())
                                    <div class="mt-2 flex items-center gap-2 text-xs">
                                        <span class="text-gray-500">{{ __('Upgrade Status') }}:</span>
                                        <x-badge :color="match ($upgradeStatus) {
                                            'Eligible' => 'green',
                                            'Closed' => 'red',
                                            default => 'gray',
                                        }">{{ __($upgradeStatus) }}</x-badge>
                                        @if ($upgradeStatus === 'Eligible')
                                            <span class="text-gray-500">{{ $enrolledCourse->pivot->upgradeDaysRemaining() }} {{ __('day(s) remaining') }}</span>
                                        @elseif ($enrolledCourse->pivot->upgradeStatusReason())
                                            <span class="text-gray-500">{{ __($enrolledCourse->pivot->upgradeStatusReason()) }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Certificates') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-2 py-1">{{ __('Certificate #') }}</th>
                                    <th class="px-2 py-1">{{ __('Course') }}</th>
                                    <th class="px-2 py-1">{{ __('Issue Date') }}</th>
                                    <th class="px-2 py-1">{{ __('Status') }}</th>
                                    <th class="px-2 py-1"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($student->certificates as $certificate)
                                    <tr>
                                        <td class="px-2 py-1 text-sm font-mono">{{ $certificate->certificate_number }}</td>
                                        <td class="px-2 py-1 text-sm">{{ $certificate->course->name }}</td>
                                        <td class="px-2 py-1 text-sm">{{ $certificate->issue_date->format('Y-m-d') }}</td>
                                        <td class="px-2 py-1 text-sm">
                                            <x-badge color="amber">{{ __('Certified') }}</x-badge>
                                        </td>
                                        <td class="px-2 py-1 text-sm">
                                            <a href="{{ route('certificates.show', $certificate) }}" class="text-sm text-amber-600 hover:underline">{{ __('View / Print') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-2 py-2 text-sm text-gray-500">{{ __('No certificates issued yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Services') }}</h3>

                    <x-input-error class="mb-2" :messages="$errors->get('service_id')" />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-2 py-1">{{ __('Service') }}</th>
                                    <th class="px-2 py-1">{{ __('Price') }}</th>
                                    <th class="px-2 py-1">{{ __('Paid') }}</th>
                                    <th class="px-2 py-1">{{ __('Balance') }}</th>
                                    <th class="px-2 py-1">{{ __('Payment Status') }}</th>
                                    <th class="px-2 py-1">{{ __('Processing Status') }}</th>
                                    <th class="px-2 py-1"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($student->studentServices as $studentService)
                                    <tr>
                                        <td class="px-2 py-1 text-sm">{{ $studentService->service->name }}</td>
                                        <td class="px-2 py-1 text-sm">{{ number_format($studentService->price, 2) }}</td>
                                        <td class="px-2 py-1 text-sm">{{ number_format($studentService->amountPaid(), 2) }}</td>
                                        <td class="px-2 py-1 text-sm">{{ number_format($studentService->balance(), 2) }}</td>
                                        <td class="px-2 py-1 text-sm">
                                            <x-badge :color="match ($studentService->status()) {
                                                'paid' => 'green',
                                                'part_payment' => 'amber',
                                                default => 'red',
                                            }">{{ __(ucwords(str_replace('_', ' ', $studentService->status()))) }}</x-badge>
                                        </td>
                                        <td class="px-2 py-1 text-sm">
                                            <form method="post" action="{{ route('student-services.processing-status.update', $studentService) }}">
                                                @csrf
                                                @method('patch')
                                                <select name="processing_status" class="border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                                    @foreach (\App\Models\StudentService::PROCESSING_STATUSES as $value)
                                                        <option value="{{ $value }}" @selected($studentService->processing_status === $value)>{{ ucwords(str_replace('_', ' ', $value)) }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                            @if ($studentService->processingProgressPercent() !== null)
                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ $studentService->processingProgressPercent() }}%
                                                    @if ($studentService->expectedReadyAt())
                                                        &middot; {{ __('Ready by :date', ['date' => $studentService->expectedReadyAt()->format('M j, Y')]) }}
                                                    @endif
                                                    @if ($studentService->isOverdueProcessing())
                                                        <x-badge color="red" class="ms-1">{{ __('Overdue') }}</x-badge>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-2 py-1 text-sm">
                                            @if (auth()->user()->isDirector() && $studentService->amountPaid() <= 0 && $studentService->processing_status === 'not_started')
                                                <form method="post" action="{{ route('student-services.destroy', $studentService) }}" class="inline" onsubmit="return confirm('{{ __('Remove this charge? This cannot be undone.') }}');">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Remove') }}</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-2 py-2 text-sm text-gray-500">{{ __('No service charges yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <x-input-error class="mt-2" :messages="$errors->get('studentService')" />

                    @if ($availableServices->isNotEmpty())
                        <form method="post" action="{{ route('students.services.store', $student) }}" class="mt-4 flex items-end gap-4">
                            @csrf

                            <div>
                                <x-input-label for="service_id" :value="__('Charge for a Service')" />
                                <select id="service_id" name="service_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                    @foreach ($availableServices as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }} (₦{{ number_format($service->price, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <x-primary-button>{{ __('Add Charge') }}</x-primary-button>
                        </form>
                    @endif
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Student Login Training') }}</h3>

                    @if (session('status') === 'training-logged')
                        <p class="mb-2 text-sm font-medium text-green-600">{{ __('Training logged successfully.') }}</p>
                    @endif
                    <x-input-error class="mb-2" :messages="$errors->get('student_id')" />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-2 py-1">{{ __('Date') }}</th>
                                    <th class="px-2 py-1">{{ __('Course') }}</th>
                                    <th class="px-2 py-1">{{ __('Type') }}</th>
                                    <th class="px-2 py-1">{{ __('Duration') }}</th>
                                    <th class="px-2 py-1">{{ __('Instructor') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($student->attendances as $attendance)
                                    <tr>
                                        <td class="px-2 py-1 text-sm">{{ $attendance->date->format('Y-m-d') }}</td>
                                        <td class="px-2 py-1 text-sm">{{ $attendance->course->name }}</td>
                                        <td class="px-2 py-1 text-sm capitalize">{{ $attendance->type ?? '—' }}</td>
                                        <td class="px-2 py-1 text-sm">{{ $attendance->duration ?? '—' }}</td>
                                        <td class="px-2 py-1 text-sm">{{ $attendance->instructor?->name ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-2 py-2 text-sm text-gray-500">{{ __('No training logins yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (auth()->user()->canManageCourses())
                    @if ($student->courses->isNotEmpty())
                        <form method="post" action="{{ route('attendances.store') }}" class="mt-4 grid grid-cols-1 sm:grid-cols-5 gap-4 items-end">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                            <input type="hidden" name="redirect_to_student" value="1">
                            <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                            <input type="hidden" name="status" value="present">

                            <div>
                                <x-input-label for="quick_login_course_id" :value="__('Course')" />
                                <select id="quick_login_course_id" name="course_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                    <option value="">{{ __('Select a course') }}</option>
                                    @foreach ($student->courses as $enrolledCourse)
                                        <option value="{{ $enrolledCourse->id }}">{{ $enrolledCourse->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="quick_login_type" :value="__('Type')" />
                                <select id="quick_login_type" name="type" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                    <option value="">{{ __('Not specified') }}</option>
                                    @foreach (['practical' => 'Practical', 'classroom' => 'Classroom'] as $value => $label)
                                        <option value="{{ $value }}">{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="quick_login_instructor_id" :value="__('Instructor')" />
                                <select id="quick_login_instructor_id" name="instructor_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach ($instructors as $availableInstructor)
                                        <option value="{{ $availableInstructor->id }}">{{ $availableInstructor->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="quick_login_duration" :value="__('Duration')" />
                                <select id="quick_login_duration" name="duration" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                    @foreach ([1 => '1 Day (Single Session)', 2 => '2 Days (Double Period / Saturday)', 3 => '3 Days (Sunday)'] as $value => $label)
                                        <option value="{{ $value }}">{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-primary-button type="submit">{{ __('Log Training') }}</x-primary-button>
                            </div>
                        </form>
                    @else
                        <p class="mt-4 text-sm text-gray-500">{{ __('Enroll this student in a course before logging training.') }}</p>
                    @endif
                    @endif
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-medium text-gray-500">{{ __('Payments') }}</h3>
                        <a href="{{ route('payments.record.create', ['student_id' => $student->id]) }}" class="text-sm text-amber-600 hover:underline">{{ __('Record a Payment') }}</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-2 py-1">{{ __('Date') }}</th>
                                    <th class="px-2 py-1">{{ __('Receipt') }}</th>
                                    <th class="px-2 py-1">{{ __('Description') }}</th>
                                    <th class="px-2 py-1">{{ __('Amount') }}</th>
                                    <th class="px-2 py-1">{{ __('Method') }}</th>
                                    <th class="px-2 py-1">{{ __('Status') }}</th>
                                    <th class="px-2 py-1">{{ __('Recorded By') }}</th>
                                    <th class="px-2 py-1"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($student->payments as $payment)
                                    <tr>
                                        <td class="px-2 py-1 text-sm">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                        <td class="px-2 py-1 text-sm font-mono text-xs">
                                            <a href="{{ route('payments.show', $payment) }}" class="text-amber-600 hover:underline">{{ $payment->receipt_number }}</a>
                                        </td>
                                        <td class="px-2 py-1 text-sm">{{ $payment->description() }}</td>
                                        <td class="px-2 py-1 text-sm">{{ number_format($payment->amount, 2) }}</td>
                                        <td class="px-2 py-1 text-sm capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                        <td class="px-2 py-1 text-sm">
                                            <x-badge :color="match ($payment->status) {
                                                'paid' => 'green',
                                                'pending' => 'amber',
                                                'failed' => 'red',
                                                'refunded' => 'blue',
                                                default => 'gray',
                                            }" class="capitalize">{{ $payment->status }}</x-badge>
                                        </td>
                                        <td class="px-2 py-1 text-sm">{{ $payment->recordedBy?->name ?? '—' }}</td>
                                        <td class="px-2 py-1 text-sm">
                                            <a href="{{ route('payments.receipt', $payment) }}" class="text-sm text-amber-600 hover:underline">{{ __('Receipt') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-2 py-2 text-sm text-gray-500">{{ __('No payments recorded yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($student->payments->where('status', 'paid')->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="px-2 py-1 text-sm font-medium text-right">{{ __('Total paid') }}</td>
                                        <td colspan="5" class="px-2 py-1 text-sm font-medium">{{ number_format($student->payments->where('status', 'paid')->sum('amount'), 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('students.edit', $student) }}">
                        <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                    </a>
                    <a href="{{ route('students.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to list') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
