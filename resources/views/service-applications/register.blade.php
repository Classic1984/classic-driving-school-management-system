{{--
    Shared "Register Applicant" page for both the Driver's License and
    Learner's Permit sections. Either path ends up at Record Payment with
    this service preselected, so there's no separate payment step here.
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Register :title Applicant', ['title' => $title]) }}
        </h2>
    </x-slot>

    @php
        $idCardIconPath = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center gap-4 px-4 sm:px-0">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $idCardIconPath }}" /></svg>
                </span>
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Register :title Applicant', ['title' => $title]) }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Link an existing student, or register someone here just for this service') }}</p>
                </div>
            </div>

            <div x-data="{ tab: 'existing' }" class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                <div class="flex gap-2 border-b border-gray-200 mb-6">
                    <button
                        type="button"
                        @click="tab = 'existing'"
                        :class="tab === 'existing' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 text-sm font-semibold border-b-2 transition"
                    >
                        {{ __('Existing Student') }}
                    </button>
                    <button
                        type="button"
                        @click="tab = 'walkin'"
                        :class="tab === 'walkin' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 text-sm font-semibold border-b-2 transition"
                    >
                        {{ __('New Walk-in Applicant') }}
                    </button>
                </div>

                <div x-show="tab === 'existing'">
                    <p class="text-sm text-gray-500 mb-4">{{ __('Already a training student? Select them below to charge and pay for :title in one step.', ['title' => $title]) }}</p>

                    <form method="get" action="{{ route('payments.record.create') }}" class="flex flex-wrap items-end gap-4">
                        <input type="hidden" name="charge_type" value="new_service">
                        <input type="hidden" name="charge_id" value="{{ $service->id }}">

                        <div class="flex-1 min-w-[16rem]">
                            <x-input-label for="student_id" :value="__('Student')" />
                            <select id="student_id" name="student_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                <option value="">{{ __('Select a student') }}</option>
                                @foreach ($students as $availableStudent)
                                    <option value="{{ $availableStudent->id }}">{{ $availableStudent->name }} ({{ $availableStudent->student_id_number }})</option>
                                @endforeach
                            </select>
                        </div>

                        <x-primary-button type="submit">{{ __('Continue to Payment') }}</x-primary-button>
                    </form>
                </div>

                <div x-show="tab === 'walkin'" x-cloak>
                    <p class="text-sm text-gray-500 mb-4">{{ __('Only here to process :title? Register their basic details, then charge and pay for it.', ['title' => $title]) }}</p>

                    <form method="post" action="{{ route("{$routePrefix}.store") }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Full Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            <div>
                                <x-input-label for="phone" :value="__('Phone')" />
                                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                            </div>

                            <div>
                                <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                                <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth')" :max="now()->subDay()->toDateString()" required />
                                <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Register & Continue to Payment') }}</x-primary-button>
                            <a href="{{ route("{$routePrefix}.index") }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
