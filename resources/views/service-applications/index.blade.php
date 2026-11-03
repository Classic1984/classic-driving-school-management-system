{{--
    The landing page for both the Driver's License and Learner's Permit
    sections - register an applicant (existing student or new walk-in)
    and take their payment in one combined form, so staff never have to
    jump to a separate payment screen to finish the job. Rendered by
    ServiceApplicationController::form() with $title/$service/$routePrefix
    varying per section - one file instead of two near-identical views.
    The applicant list lives at "{routePrefix}.applicants" instead - see
    the link below.
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    @php
        $idCardIconPath = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';
        $activeTab = old('mode', 'existing');
    @endphp

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                        <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $idCardIconPath }}" /></svg>
                    </span>
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900">{{ $title }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Register the applicant and take their payment in one step') }}</p>
                    </div>
                </div>

                <a href="{{ route("{$routePrefix}.applicants") }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    {{ __('View Applicants') }}
                </a>
            </div>

            <div x-data="{ tab: '{{ $activeTab }}' }" class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
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
                        @click="tab = 'walk_in'"
                        :class="tab === 'walk_in' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 text-sm font-semibold border-b-2 transition"
                    >
                        {{ __('New Walk-in Applicant') }}
                    </button>
                </div>

                <div x-show="tab === 'existing'">
                    <p class="text-sm text-gray-500 mb-4">{{ __('Already a training student? Select them below, then confirm the payment.') }}</p>

                    <form method="post" action="{{ route("{$routePrefix}.store") }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="mode" value="existing">

                        <div>
                            <x-input-label for="student_id" :value="__('Student')" />
                            <select id="student_id" name="student_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                <option value="">{{ __('Select a student') }}</option>
                                @foreach ($students as $availableStudent)
                                    <option value="{{ $availableStudent->id }}" @selected((string) old('student_id') === (string) $availableStudent->id)>{{ $availableStudent->name }} ({{ $availableStudent->student_id_number }})</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('student_id')" />
                        </div>

                        @include('service-applications.partials.payment-fields', ['idPrefix' => 'existing'])

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Register & Record Payment') }}</x-primary-button>
                        </div>
                    </form>
                </div>

                <div x-show="tab === 'walk_in'" x-cloak>
                    <p class="text-sm text-gray-500 mb-4">{{ __('Only here to process :title? Register their basic details, then confirm the payment.', ['title' => $title]) }}</p>

                    <form method="post" action="{{ route("{$routePrefix}.store") }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="mode" value="walk_in">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Full Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
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

                        @include('service-applications.partials.payment-fields', ['idPrefix' => 'walk_in'])

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Register & Record Payment') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
