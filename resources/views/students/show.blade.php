<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg space-y-4">
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
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->occupation ?? '—' }}</dd>
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
                        <dt class="text-sm font-medium text-gray-500">{{ __('Requires Classes') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ is_null($student->requires_classes) ? '—' : ($student->requires_classes ? __('Yes') : __('No')) }}</dd>
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
                        <dd class="text-sm text-gray-900 col-span-2 capitalize">{{ $student->status }}</dd>
                    </div>
                </dl>

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
                    <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Enrollments') }}</h3>

                    @if (session('status') === 'enrollment-completed')
                        <p class="mb-2 text-sm font-medium text-green-600">{{ __('Course marked as completed.') }}</p>
                    @endif
                    <x-input-error class="mb-2" :messages="$errors->get('enrollment')" />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-2 py-1">{{ __('Course') }}</th>
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
                                        <td class="px-2 py-1 text-sm">{{ number_format($enrolledCourse->pivot->balance(), 2) }}</td>
                                        <td class="px-2 py-1 text-sm">{{ optional($enrolledCourse->pivot->due_date)->format('Y-m-d') ?? '—' }}</td>
                                        <td class="px-2 py-1 text-sm font-semibold capitalize
                                            @if ($enrolledCourse->pivot->status === 'locked') text-red-600
                                            @elseif ($enrolledCourse->pivot->status === 'completed') text-blue-600
                                            @else text-green-600
                                            @endif">
                                            {{ $enrolledCourse->pivot->status }}
                                        </td>
                                        <td class="px-2 py-1 text-sm">
                                            @if ($enrolledCourse->pivot->status !== 'completed' && $enrolledCourse->pivot->balance() <= 0)
                                                <form method="post" action="{{ route('enrollments.complete', $enrolledCourse->pivot->id) }}" class="inline">
                                                    @csrf
                                                    @method('patch')
                                                    <button type="submit" class="text-sm text-amber-600 hover:underline">{{ __('Mark Complete') }}</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-2 py-2 text-sm text-gray-500">{{ __('Not enrolled in any courses yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Payments') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-2 py-1">{{ __('Date') }}</th>
                                    <th class="px-2 py-1">{{ __('Course') }}</th>
                                    <th class="px-2 py-1">{{ __('Amount') }}</th>
                                    <th class="px-2 py-1">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($student->payments as $payment)
                                    <tr>
                                        <td class="px-2 py-1 text-sm">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                        <td class="px-2 py-1 text-sm">{{ $payment->course->name }}</td>
                                        <td class="px-2 py-1 text-sm">{{ number_format($payment->amount, 2) }}</td>
                                        <td class="px-2 py-1 text-sm capitalize">{{ $payment->status }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-2 py-2 text-sm text-gray-500">{{ __('No payments recorded yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($student->payments->where('status', 'paid')->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="px-2 py-1 text-sm font-medium text-right">{{ __('Total paid') }}</td>
                                        <td colspan="2" class="px-2 py-1 text-sm font-medium">{{ number_format($student->payments->where('status', 'paid')->sum('amount'), 2) }}</td>
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
