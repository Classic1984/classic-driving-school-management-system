@props(['logs'])

@if ($logs->isEmpty())
    <p class="text-sm text-gray-500">{{ __('No activity recorded yet.') }}</p>
@else
    <div {{ $attributes }}>
        @foreach ($logs as $log)
            @php
                $iconMeta = $log->iconMeta();
                $iconColorClasses = match ($iconMeta['color']) {
                    'sky' => 'bg-sky-100 text-sky-600',
                    'purple' => 'bg-purple-100 text-purple-600',
                    'red' => 'bg-red-100 text-red-600',
                    'green' => 'bg-green-100 text-green-600',
                    'blue' => 'bg-blue-100 text-blue-600',
                    'amber' => 'bg-amber-100 text-amber-600',
                    'indigo' => 'bg-indigo-100 text-indigo-600',
                    'orange' => 'bg-orange-100 text-orange-600',
                    'teal' => 'bg-teal-100 text-teal-600',
                    default => 'bg-gray-100 text-gray-500',
                };
            @endphp
            <div class="relative flex gap-3 pb-5 last:pb-0">
                @unless ($loop->last)
                    <span class="absolute left-[1.125rem] top-9 bottom-0 w-px bg-gray-200"></span>
                @endunless
                <span class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $iconColorClasses }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconMeta['icon'] }}" /></svg>
                </span>
                <div class="flex-1 pt-1">
                    <p class="text-sm text-gray-800">{{ $log->description }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $log->created_at->format('M j, Y g:i A') }}
                        @if ($log->user)
                            {{ __('by') }} {{ $log->user->name }}
                        @endif
                    </p>
                </div>
            </div>
        @endforeach
    </div>
@endif
