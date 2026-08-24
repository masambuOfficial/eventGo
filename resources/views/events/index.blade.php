<x-layouts.app title="Your events">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-[24px] font-semibold text-ink">Your events</h1>
            <a href="{{ route('events.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition">
                + New event
            </a>
        </div>

        @forelse ($events as $event)
            <a href="{{ route('events.dashboard', $event) }}"
               class="block bg-surface-raised border border-line rounded-lg p-6 mb-4 hover:border-green-600 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[16px] font-medium text-ink">{{ $event->name }}</p>
                        <p class="text-[13px] text-slate">
                            {{ $event->eventType->name }} — {{ $event->starts_at->format('j M Y') }}
                            @if ($event->district) · {{ $event->district->name }} @endif
                        </p>
                    </div>
                    <span class="text-[13px] text-slate bg-surface border border-line rounded-full px-2 py-0.5">
                        {{ ucfirst($event->status) }}
                    </span>
                </div>
            </a>
        @empty
            <div class="bg-surface-raised border border-line rounded-lg p-6 text-center">
                <p class="text-[14px] text-slate mb-3">No events yet.</p>
                <a href="{{ route('events.create') }}" class="text-[14px] text-green-600 hover:underline">Plan your first event</a>
            </div>
        @endforelse
    </div>
</x-layouts.app>
