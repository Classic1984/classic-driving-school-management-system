<div class="relative inline-block text-left" x-data="{ open: false }">
    <button type="button" @click="open = !open" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-amber-100 hover:text-amber-600 transition">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c.621 0 1.125-.504 1.125-1.125S12.621 4.5 12 4.5s-1.125.504-1.125 1.125S11.379 6.75 12 6.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 10.5 12 10.5s-1.125.504-1.125 1.125S11.379 12.75 12 12.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 16.5 12 16.5s-1.125.504-1.125 1.125S11.379 18.75 12 18.75Z" /></svg>
    </button>
    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-gray-200 py-1 z-10">
        <a href="{{ route('courses.show', $course) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('View') }}</a>
        @if (auth()->user()->canManageCourses())
            <a href="{{ route('courses.edit', $course) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
        @endif
        @if (auth()->user()->isAdmin())
            <form method="post" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this course?') }}');">
                @csrf
                @method('delete')
                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
            </form>
        @endif
    </div>
</div>
