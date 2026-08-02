<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificates') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Certificates') }}
                    </h3>

                    <a href="{{ route('certificates.create') }}">
                        <x-primary-button type="button">{{ __('Issue Certificate') }}</x-primary-button>
                    </a>
                </div>

                @if (session('status') === 'certificate-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Certificate issued successfully.') }}</p>
                @elseif (session('status') === 'certificate-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Certificate updated successfully.') }}</p>
                @elseif (session('status') === 'certificate-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Certificate removed successfully.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Certificate #') }}</th>
                                <th class="px-4 py-2">{{ __('Issue Date') }}</th>
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Course') }}</th>
                                <th class="px-4 py-2">{{ __('Instructor') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($certificates as $certificate)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $certificate->certificate_number }}</td>
                                    <td class="px-4 py-2">{{ $certificate->issue_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">{{ $certificate->student->name }}</td>
                                    <td class="px-4 py-2">{{ $certificate->course->name }}</td>
                                    <td class="px-4 py-2">{{ $certificate->instructor?->name ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('certificates.show', $certificate) }}" class="text-sm text-indigo-600 hover:underline">{{ __('View') }}</a>
                                        <a href="{{ route('certificates.edit', $certificate) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                        @if (auth()->user()->isAdmin())
                                            <form method="post" action="{{ route('certificates.destroy', $certificate) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to revoke this certificate?') }}');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Revoke') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No certificates issued yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $certificates->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
