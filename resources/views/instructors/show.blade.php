<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Instructor Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-4">
                <dl class="divide-y divide-gray-100">
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $instructor->name }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Email') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $instructor->email }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Phone') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $instructor->phone }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('License Number') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $instructor->license_number ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Specialization') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 capitalize">{{ $instructor->specialization }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Hire Date') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $instructor->hire_date->format('Y-m-d') }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <x-badge :color="$instructor->status === 'active' ? 'green' : 'gray'" class="capitalize">{{ $instructor->status }}</x-badge>
                        </dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Courses') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            @forelse ($instructor->courses as $course)
                                <div>{{ $course->name }}</div>
                            @empty
                                —
                            @endforelse
                        </dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('App Access') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            @if ($instructor->hasAppAccess())
                                <x-badge :color="$instructor->user->pin_set_at ? 'green' : 'amber'">
                                    {{ $instructor->user->pin_set_at ? __('Active') : __('Pending first login') }}
                                </x-badge>
                                @if (auth()->user()->canManageCourses())
                                    <form method="post" action="{{ route('instructors.access.destroy', $instructor) }}" class="inline ms-2" onsubmit="return confirm('{{ __('Revoke this instructor\'s app access? Their PIN will stop working immediately.') }}');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Revoke Access') }}</button>
                                    </form>
                                @endif
                            @else
                                <x-badge color="gray">{{ __('Not Enabled') }}</x-badge>
                                @if (auth()->user()->canManageCourses())
                                    <form method="post" action="{{ route('instructors.access.store', $instructor) }}" class="inline ms-2">
                                        @csrf
                                        <button type="submit" class="text-sm text-amber-600 hover:underline">{{ __('Enable App Access') }}</button>
                                    </form>
                                @endif
                            @endif
                        </dd>
                    </div>
                </dl>

                @if (session('status') === 'instructor-access-granted')
                    <p class="text-sm font-medium text-green-600">{{ __('App access granted - the instructor has been texted a login link.') }}</p>
                @elseif (session('status') === 'instructor-access-revoked')
                    <p class="text-sm font-medium text-green-600">{{ __('App access revoked.') }}</p>
                @endif
                <x-input-error :messages="$errors->get('instructor')" />

                <div class="flex items-center gap-4">
                    @if (auth()->user()->canManageCourses())
                        <a href="{{ route('instructors.edit', $instructor) }}">
                            <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                        </a>
                    @endif
                    <a href="{{ route('instructors.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to list') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
