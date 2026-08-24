<x-layouts.app title="{{ $event->name }}">
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('events.index') }}" class="text-[13px] text-slate hover:text-ink">&larr; Your events</a>

        <div class="flex items-start justify-between mt-2 mb-6">
            <div>
                <h1 class="text-[24px] font-semibold text-ink">{{ $event->name }}</h1>
                <p class="text-[14px] text-slate">
                    {{ $event->eventType->name }} — {{ $event->starts_at->format('j M Y') }}
                    @if ($event->district) · {{ $event->district->name }} @endif
                    @if ($event->guest_count_expected) · {{ number_format($event->guest_count_expected) }} guests @endif
                </p>
            </div>
            <a href="{{ route('events.wizard', $event) }}" class="text-[13px] text-green-600 hover:underline">
                Edit details
            </a>
        </div>

        <div class="bg-surface-raised border border-line rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[14px] font-medium text-ink">Planning progress</p>
                <p class="text-[14px] text-slate">{{ $event->planning_progress }}%</p>
            </div>
            <div class="h-2 bg-line rounded-full overflow-hidden">
                <div class="h-full bg-green-600" style="width: {{ $event->planning_progress }}%"></div>
            </div>
        </div>

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-[18px] font-semibold text-ink">Requirements</h2>
            @if ($event->budget_total_ugx)
                <span class="text-[16px] font-semibold text-ink">UGX {{ number_format($event->budget_total_ugx) }}</span>
            @endif
        </div>

        @php
            $statusStyles = [
                'draft' => 'bg-surface text-slate', 'open' => 'bg-surface text-slate',
                'sourcing' => 'bg-amber-100 text-amber-700', 'offers_received' => 'bg-amber-100 text-amber-700',
                'shortlisted' => 'bg-green-100 text-green-900', 'awarded' => 'bg-green-100 text-green-900',
                'booked' => 'bg-green-600 text-white', 'fulfilled' => 'bg-green-600 text-white',
                'no_offers' => 'bg-surface text-slate', 'dropped' => 'bg-surface text-slate',
            ];
        @endphp

        @forelse ($event->requirements as $requirement)
            <div class="bg-surface-raised border border-line rounded-lg p-4 mb-3 flex items-center justify-between">
                <div>
                    <p class="text-[14px] font-medium text-ink">{{ $requirement->title }}</p>
                    <p class="text-[13px] text-slate">
                        {{ $requirement->quantity ? rtrim(rtrim(number_format($requirement->quantity, 2), '0'), '.') : '' }}
                        {{ $requirement->unit }} · {{ ucfirst($requirement->priority) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if ($requirement->budget_estimate_ugx)
                        <span class="text-[14px] text-ink">UGX {{ number_format($requirement->budget_estimate_ugx) }}</span>
                    @endif
                    <span class="text-[12px] font-medium px-2 py-0.5 rounded-full {{ $statusStyles[(string) $requirement->status] ?? 'bg-surface text-slate' }}">
                        {{ ucfirst(str_replace('_', ' ', (string) $requirement->status)) }}
                    </span>
                    <a href="{{ route('sourcing.show', $requirement) }}" class="text-[13px] text-green-600 hover:underline">Source</a>
                </div>
            </div>
        @empty
            <div class="bg-surface-raised border border-line rounded-lg p-6 text-center">
                <p class="text-[14px] text-slate mb-3">No requirements yet.</p>
                <a href="{{ route('events.wizard', $event) }}" class="text-[14px] text-green-600 hover:underline">Build your requirements list</a>
            </div>
        @endforelse
    </div>
</x-layouts.app>
