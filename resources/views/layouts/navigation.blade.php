<!-- Mobile backdrop -->
<div @click="sidebarOpen = false" :class="{ 'block': sidebarOpen, 'hidden': ! sidebarOpen }" class="hidden fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

<!-- Sidebar -->
<aside
    :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': ! sidebarOpen }"
    class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-black transition-transform duration-200 ease-in-out lg:translate-x-0"
>
    <div class="flex h-16 shrink-0 items-center justify-between px-4 border-b border-gray-800">
        <a href="{{ route('dashboard') }}">
            <x-application-logo class="block h-9 w-auto" />
        </a>
        <button @click="sidebarOpen = false" class="text-gray-400 hover:text-amber-400 lg:hidden">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-2 py-4 space-y-1">
        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            {{ __('Dashboard') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('students.index')" :active="request()->routeIs('students.*')">
            {{ __('Students') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('leads.index')" :active="request()->routeIs('leads.*')">
            {{ __('Leads') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')">
            {{ __('Payments') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('certificates.index')" :active="request()->routeIs('certificates.*')">
            {{ __('Certificates') }}
        </x-responsive-nav-link>

        <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('Training') }}</p>
        <x-responsive-nav-link :href="route('courses.index')" :active="request()->routeIs('courses.*')">
            {{ __('Courses') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('instructors.index')" :active="request()->routeIs('instructors.*')">
            {{ __('Instructors') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('vehicles.index')" :active="request()->routeIs('vehicles.*')">
            {{ __('Vehicles') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('enrolled-trainees.index')" :active="request()->routeIs('enrolled-trainees.*') || request()->routeIs('attendances.*') || request()->routeIs('students.training-record')">
            {{ __('Student Login Training') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('theory-classes.index')" :active="request()->routeIs('theory-classes.*')">
            {{ __('Theory Classes') }}
        </x-responsive-nav-link>

        @if (auth()->user()->isDirector())
            <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('Admin') }}</p>
            <x-responsive-nav-link :href="route('approvals.index')" :active="request()->routeIs('approvals.*')">
                {{ __('Approval Centre') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('finance.summary')" :active="request()->routeIs('finance.*') || request()->routeIs('expenses.*')">
                {{ __('Finance') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('services.index')" :active="request()->routeIs('services.*')">
                {{ __('Services') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('theory-class-cancellations.index')" :active="request()->routeIs('theory-class-cancellations.*')">
                {{ __('Theory Class') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('activity-log.index')" :active="request()->routeIs('activity-log.*')">
                {{ __('Activity Log') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('message-log.index')" :active="request()->routeIs('message-log.*')">
                {{ __('Message Log') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                {{ __('Staff') }}
            </x-responsive-nav-link>
        @endif
    </nav>

    <div class="shrink-0 border-t border-gray-800 p-4 space-y-3">
        @include('partials.push-notifications', ['sidebar' => true])

        <div class="font-medium text-sm text-white truncate">{{ Auth::user()->name }}</div>
        <div class="font-medium text-xs text-gray-400 truncate">{{ Auth::user()->email }}</div>

        <div class="mt-3 space-y-1">
            <x-responsive-nav-link :href="route('profile.edit')">
                {{ __('Profile') }}
            </x-responsive-nav-link>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                    this.closest('form').submit();">
                    {{ __('Log Out') }}
                </x-responsive-nav-link>
            </form>
        </div>
    </div>
</aside>
