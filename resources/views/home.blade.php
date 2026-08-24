<x-layouts.app title="Home">
    <h1 class="text-[24px] font-semibold text-ink mb-6">Welcome, {{ auth()->user()->full_name }}</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if ($eventCount > 0)
            <a href="{{ route('events.index') }}"
               class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition">
                <p class="text-[18px] font-semibold text-ink mb-1">Your events</p>
                <p class="text-[14px] text-slate mb-3">{{ $eventCount }} {{ Str::plural('event', $eventCount) }} planned so far.</p>
                <span class="text-[14px] text-green-600">View your events &rarr;</span>
            </a>
        @else
            <a href="{{ route('events.create') }}"
               class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition">
                <p class="text-[18px] font-semibold text-ink mb-1">Plan an event</p>
                <p class="text-[14px] text-slate mb-3">Turn your event into a costed requirements list and source providers against it.</p>
                <span class="text-[14px] text-green-600">Get started &rarr;</span>
            </a>
        @endif

        @if ($provider)
            <a href="{{ route('provider.dashboard') }}"
               class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition">
                <p class="text-[18px] font-semibold text-ink mb-1">{{ $provider->business_name }}</p>
                <p class="text-[14px] text-slate mb-3">Profile {{ $provider->profile_completeness }}% complete</p>
                <span class="text-[14px] text-green-600">Go to your provider dashboard &rarr;</span>
            </a>
        @else
            <a href="{{ route('provider.onboarding') }}"
               class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition">
                <p class="text-[18px] font-semibold text-ink mb-1">Offer your services</p>
                <p class="text-[14px] text-slate mb-3">Set up your provider profile and start getting qualified opportunities.</p>
                <span class="text-[14px] text-green-600">Get started &rarr;</span>
            </a>
        @endif
    </div>
</x-layouts.app>
