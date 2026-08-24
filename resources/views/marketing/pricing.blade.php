<x-layouts.marketing title="Pricing — Event Go" description="Provider plans and pricing. Posting an event as an organiser is always free.">
    <div class="mkt-band relative overflow-hidden" style="clip-path: polygon(0 0, 100% 0, 100% 94%, 0 100%);">
        <div class="mkt-mesh" aria-hidden="true"></div>
        <div class="mkt-dotgrid" aria-hidden="true"></div>
        <div class="max-w-4xl mx-auto px-4 py-20 md:py-24 relative z-10">
            <h1 class="mkt-reveal text-[36px] md:text-[44px] font-bold text-white tracking-tight mb-4">Pricing</h1>
            <p class="mkt-reveal text-[16px] text-white/75 max-w-2xl" data-reveal-delay="80">
                Planning an event and requesting offers is free for organisers — Event Go doesn't have an
                organiser plan yet, and takes no commission on what you agree with a provider. The plans below
                are for providers.
            </p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-16 md:py-20">
        @php
            // Tailwind's build scans blade source for literal class strings,
            // so the column count can't be interpolated — it has to be one
            // of these known, statically-scannable classes.
            $gridClass = match (min($plans->count(), 4)) {
                1 => 'md:grid-cols-1',
                2 => 'md:grid-cols-2',
                3 => 'md:grid-cols-3',
                default => 'md:grid-cols-4',
            };
        @endphp
        <div class="grid grid-cols-1 {{ $gridClass }} gap-4 mb-4">
            @foreach ($plans as $plan)
                @php $entitlements = $plan->entitlements ?? []; @endphp
                <div class="mkt-reveal bg-surface-raised border border-line rounded-lg p-6 flex flex-col transition-all duration-300 hover:-translate-y-1.5 hover:border-green-600 hover:shadow-[0_18px_36px_-18px_rgba(20,112,92,.35)]"
                     data-reveal-delay="{{ $loop->index * 70 }}">
                    <h2 class="text-[16px] font-semibold text-ink mb-1">{{ $plan->name }}</h2>
                    <p class="text-[26px] font-bold text-ink mb-1 tabular-nums">
                        UGX {{ number_format($plan->price_ugx) }}
                    </p>
                    <p class="text-[13px] text-slate mb-4">{{ $plan->duration_days }} days</p>

                    <ul class="space-y-2 text-[13px] text-ink flex-1">
                        <li>
                            {{ ($entitlements['max_offers_per_month'] ?? null) === null ? 'Unlimited offers' : $entitlements['max_offers_per_month'].' offers per month' }}
                        </li>
                        @if (isset($entitlements['portfolio_slots']))
                            <li>{{ $entitlements['portfolio_slots'] }} portfolio photos</li>
                        @endif
                        @if (($entitlements['analytics'] ?? false))
                            <li>Analytics on your opportunities and offers</li>
                        @endif
                        @if (($entitlements['featured_eligible'] ?? false))
                            <li>Eligible for featured placement</li>
                        @endif
                    </ul>

                    <a href="{{ route('register', ['intent' => 'provider']) }}"
                       class="mt-6 block text-center border border-line hover:border-green-600 hover:bg-green-50 text-ink rounded-sm px-4 py-2 text-[14px] font-medium transition-colors duration-200">
                        Get started
                    </a>
                </div>
            @endforeach
        </div>

        <p class="text-[13px] text-slate mb-16">
            These are launch prices and may change as the platform grows.
        </p>

        <div class="mkt-reveal bg-green-50 border border-line rounded-lg p-8">
            <h2 class="text-[20px] font-bold text-ink tracking-tight mb-3">No commission, ever</h2>
            <p class="text-[14px] text-ink max-w-2xl">
                Event Go is not a party to the deal you make with an organiser or provider. We don't hold
                funds, we don't mediate disputes, and we don't take a cut of what you agree. Plans pay for
                visibility and tools on the platform — nothing more.
            </p>
        </div>
    </div>
</x-layouts.marketing>
