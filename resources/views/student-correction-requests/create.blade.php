<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Request a Correction') }}
        </h2>
    </x-slot>

    @php
        $lockIconPath = 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z';
    @endphp

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-gradient-to-r from-navy-900 via-navy-800 to-navy px-4 py-6 sm:px-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-gold-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $lockIconPath }}" />
                    </svg>

                    <div class="relative flex items-center gap-4 min-w-0">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gold-500/15 text-gold-400 ring-1 ring-gold-400/30">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $lockIconPath }}" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-xl sm:text-2xl font-extrabold text-white truncate">{{ __('Request a Correction') }}</h3>
                            <p class="text-sm text-gray-400 truncate">{{ $student->name }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-8 space-y-6">
                    <p class="text-sm text-gray-600">
                        {{ __('This information can only be changed by a Director. Describe what needs to change and why - a Director will review it and make the correction.') }}
                    </p>

                    <form method="post" action="{{ route('student-correction-requests.store', $student) }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="field" :value="__('Field')" />
                            <x-select id="field" name="field" class="mt-1 block w-full" required>
                                @foreach (['name' => 'Name', 'date_of_birth' => 'Date of Birth', 'phone' => 'Phone', 'program' => 'Training Program'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('field', $field) === $value)>{{ __($label) }}</option>
                                @endforeach
                            </x-select>
                            <x-input-error class="mt-2" :messages="$errors->get('field')" />
                        </div>

                        <div>
                            <x-input-label :value="__('Current Value')" />
                            <p class="mt-1 text-sm text-gray-900">
                                {{ match ($field) {
                                    'name' => $student->name,
                                    'date_of_birth' => optional($student->date_of_birth)->format('l, M j, Y') ?? '—',
                                    'phone' => $student->phone,
                                    'program' => $student->courses->pluck('name')->implode(', ') ?: '—',
                                    default => '—',
                                } }}
                            </p>
                        </div>

                        <div>
                            <x-input-label for="requested_value" :value="__('Requested Value')" />
                            <x-text-input id="requested_value" name="requested_value" type="text" class="mt-1 block w-full" :value="old('requested_value')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('requested_value')" />
                        </div>

                        <div>
                            <x-input-label for="reason" :value="__('Reason (optional)')" />
                            <x-textarea id="reason" name="reason" rows="3" class="mt-1 block w-full">{{ old('reason') }}</x-textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-line">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-navy hover:bg-navy-700 px-5 py-3 text-sm font-bold text-gold-400 transition">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                {{ __('Submit Request') }}
                            </button>
                            <a href="{{ route('students.show', $student) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-line hover:bg-paper px-5 py-3 text-sm font-semibold text-gray-700 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
