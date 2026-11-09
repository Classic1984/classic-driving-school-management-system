<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Inquiry') }}
        </h2>
    </x-slot>

    @php
        $inquiryIconPath = 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.769Z';
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-gradient-to-r from-navy-900 via-navy-800 to-navy px-4 py-6 sm:px-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-gold-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $inquiryIconPath }}" /></svg>

                    <div class="relative flex items-center gap-4 min-w-0">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gold-500/15 text-gold-400 ring-1 ring-gold-400/30">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $inquiryIconPath }}" /></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-xl sm:text-2xl font-extrabold text-white truncate">{{ __('Edit Inquiry') }}</h3>
                            <p class="text-sm text-gray-400 truncate">{{ $lead->name }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-8 space-y-6">
                    <form method="post" action="{{ route('leads.update', $lead) }}" class="space-y-6">
                        @csrf
                        @method('put')

                        @include('leads.partials.form-fields')

                        <div class="flex items-center gap-4 pt-4 border-t border-line">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <a href="{{ route('leads.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                        </div>
                    </form>

                    <div class="pt-6 border-t border-line">
                        <a href="{{ route('students.create', ['name' => $lead->name, 'phone' => $lead->phone]) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gold-600 hover:underline">
                            {{ __('Register This Lead as a Student') }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /></svg>
                        </a>
                        <p class="mt-1 text-xs text-gray-500">{{ __("Pre-fills the student registration form with this lead's name and phone. Remember to mark this inquiry Converted once registration is complete.") }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
