<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Training Ticket') }}
        </h2>
    </x-slot>

    <div class="py-12 print:py-0">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">
            <div class="p-6 bg-white shadow sm:rounded-lg border-2 border-dashed border-gray-300 print:border-0 print:shadow-none">
                <div class="text-center mb-4">
                    <h3 class="text-lg font-bold">{{ __('Classic Driving School & Son Nigeria Limited') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Training Pass') }}</p>
                </div>

                <dl class="divide-y divide-gray-100">
                    <div class="py-2 grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Ticket #') }}</dt>
                        <dd class="text-sm text-gray-900 font-mono">{{ $ticket->ticket_number }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Student Name') }}</dt>
                        <dd class="text-sm text-gray-900 font-semibold">{{ $ticket->student->name }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Student ID') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $ticket->student->id }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Course') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $ticket->course->name }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Date') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $ticket->date->format('Y-m-d') }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Instructor') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $ticket->instructor?->name ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Vehicle') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $ticket->vehicle ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Lesson #') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $ticket->lesson_number ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Payment Status') }}</dt>
                        <dd class="text-sm font-semibold text-green-600 capitalize">{{ $ticket->payment_status }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6 flex items-center gap-4 print:hidden">
                <x-secondary-button type="button" onclick="window.print()">{{ __('Print') }}</x-secondary-button>
                <a href="{{ route('tickets.edit', $ticket) }}">
                    <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                </a>
                <a href="{{ route('tickets.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to list') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
