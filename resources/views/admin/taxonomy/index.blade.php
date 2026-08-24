<x-layouts.app title="Taxonomy">
    <a href="{{ route('admin.dashboard') }}" class="text-[13px] text-slate hover:text-ink">&larr; Admin</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-6">Taxonomy</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('admin.taxonomy.event-types') }}" class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition-colors duration-200">
            <h2 class="text-[16px] font-semibold text-ink mb-1">Event types</h2>
            <p class="text-[14px] text-slate">Wedding, birthday, conference — the kinds of event an organiser can plan.</p>
        </a>
        <a href="{{ route('admin.taxonomy.service-categories') }}" class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition-colors duration-200">
            <h2 class="text-[16px] font-semibold text-ink mb-1">Service categories</h2>
            <p class="text-[14px] text-slate">What providers offer — catering, venues, decoration, and their sub-categories.</p>
        </a>
        <a href="{{ route('admin.taxonomy.districts') }}" class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition-colors duration-200">
            <h2 class="text-[16px] font-semibold text-ink mb-1">Districts</h2>
            <p class="text-[14px] text-slate">Where providers say they serve. Ongoing maintenance, not a one-time list.</p>
        </a>
        <div class="bg-surface-raised border border-line rounded-lg p-6">
            <h2 class="text-[16px] font-semibold text-ink mb-1">Scope questions &amp; requirement templates</h2>
            <p class="text-[14px] text-slate mb-3">Pick an event type below to manage its questions and generated requirements.</p>
            <div class="space-y-1">
                @foreach ($eventTypes as $eventType)
                    <div class="flex items-center justify-between text-[13px]">
                        <span class="text-ink">{{ $eventType->name }}</span>
                        <span class="flex gap-3">
                            <a href="{{ route('admin.taxonomy.scope-questions', $eventType) }}" class="text-green-600 hover:underline">Questions</a>
                            <a href="{{ route('admin.taxonomy.requirement-templates', $eventType) }}" class="text-green-600 hover:underline">Templates</a>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
