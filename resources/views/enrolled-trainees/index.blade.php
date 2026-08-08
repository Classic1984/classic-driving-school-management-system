<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enrolled Trainees') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Enrolled Trainees') }}
                    </h3>

                    <a href="{{ route('attendances.index') }}" class="text-sm text-amber-600 hover:underline">{{ __('View All Training Logins') }}</a>
                </div>

                @if (session('status') === 'training-logged')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Training logged successfully.') }}</p>
                @endif

                <form method="get" action="{{ route('enrolled-trainees.index') }}" class="flex gap-2 max-w-xl mb-6">
                    <x-text-input name="search" type="text" class="flex-1" placeholder="{{ __('Name, email, or phone') }}" :value="request('search')" />
                    <x-primary-button type="submit">{{ __('Search') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Application Date') }}</th>
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Date of Birth') }}</th>
                                <th class="px-4 py-2">{{ __('Course') }}</th>
                                <th class="px-4 py-2">{{ __('Phone Number') }}</th>
                                <th class="px-4 py-2">{{ __('E-Mail') }}</th>
                                <th class="px-4 py-2">{{ __('Training Sessions') }}</th>
                                <th class="px-4 py-2">{{ __('Last Modified') }}</th>
                                <th class="px-4 py-2">{{ __('Modified By') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($trainees as $trainee)
                                @php ($lastLogin = $trainee->attendances->first())
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $trainee->enrollment_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <a href="{{ route('students.training-record', $trainee) }}" class="text-amber-600 hover:underline font-medium">
                                            {{ $trainee->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ optional($trainee->date_of_birth)->format('Y-m-d') ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $trainee->courses->pluck('name')->implode(', ') ?: '—' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $trainee->phone }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $trainee->email }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $trainee->attendances->count() }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $lastLogin?->updated_at->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $lastLogin?->loggedBy?->name ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap">
                                        <a href="{{ route('students.training-record', $trainee) }}" class="text-sm text-amber-600 hover:underline">{{ __('Log Training') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No enrolled trainees yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $trainees->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
