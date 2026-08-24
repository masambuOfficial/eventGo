<x-layouts.marketing title="Event Go — plan events, find providers, no commission">
    <x-marketing.hero>
        <x-slot:heading>Turn your event into a plan in minutes</x-slot:heading>
        <x-slot:subcopy>
            Answer a few questions about your event and get a costed, editable requirements list —
            then source real providers against it. Posting is free, and Event Go never takes a cut.
        </x-slot:subcopy>
        <x-slot:cta>
            <a href="{{ route('register', ['intent' => 'organiser']) }}"
               class="bg-green-600 hover:bg-green-700 hover:-translate-y-0.5 hover:shadow-[0_12px_28px_-8px_rgba(46,156,130,.6)] text-white rounded-sm px-5 py-3 text-[14px] font-semibold transition-all duration-200">
                Plan an event
            </a>
            <a href="{{ route('register', ['intent' => 'provider']) }}"
               class="border border-white/25 hover:border-amber-500 hover:-translate-y-0.5 text-white rounded-sm px-5 py-3 text-[14px] font-semibold transition-all duration-200">
                Offer your services
            </a>
        </x-slot:cta>
        <x-slot:visual>
            <div class="bg-surface-raised border border-line rounded-lg shadow-sm p-5 max-w-sm">
                <p class="text-[12px] text-slate mb-3">Example — 500-guest wedding, Kampala</p>
                <dl class="divide-y divide-line text-[13px]">
                    <div class="flex justify-between py-2">
                        <dt class="text-ink">Catering — 500 plates</dt>
                        <dd class="text-slate">UGX 15,000,000</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-ink">Chairs — 525</dt>
                        <dd class="text-slate">UGX 2,600,000</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-ink">Tents — 4</dt>
                        <dd class="text-slate">UGX 4,000,000</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-ink">Ushers — 10</dt>
                        <dd class="text-slate">UGX 1,000,000</dd>
                    </div>
                </dl>
                <div class="flex justify-between pt-3 mt-1 border-t border-line text-[14px] font-medium">
                    <span class="text-ink">Estimated total</span>
                    <span class="text-ink">UGX 37,700,000</span>
                </div>
            </div>
        </x-slot:visual>
    </x-marketing.hero>

    <x-marketing.category-grid
        heading="What kind of event are you planning"
        :items="$eventTypes"
        intent="organiser"
    />

    <div class="bg-surface-raised border-y border-line">
        <x-marketing.category-grid
            heading="What do you offer"
            :items="$categories"
            intent="provider"
            :show-children="true"
        />
    </div>

    <div class="max-w-6xl mx-auto px-4 py-16 md:py-20">
        <div class="text-center max-w-xl mx-auto mb-10">
            <h2 class="mkt-reveal text-[26px] md:text-[30px] font-bold text-ink tracking-tight mb-2">How it works</h2>
            <p class="mkt-reveal text-[15px] text-slate">Three steps from a vague idea to a booked event.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="mkt-reveal bg-surface-raised border border-line rounded-lg p-6" data-reveal-delay="0">
                <span class="block w-8 h-8 rounded-full bg-green-100 text-green-900 text-[13px] font-bold flex items-center justify-center mb-4">1</span>
                <p class="text-[15px] font-semibold text-ink mb-1">Describe your event</p>
                <p class="text-[14px] text-slate">Answer a few questions and get a costed requirements list in minutes.</p>
            </div>
            <div class="mkt-reveal bg-surface-raised border border-line rounded-lg p-6" data-reveal-delay="90">
                <span class="block w-8 h-8 rounded-full bg-green-100 text-green-900 text-[13px] font-bold flex items-center justify-center mb-4">2</span>
                <p class="text-[15px] font-semibold text-ink mb-1">Compare real offers</p>
                <p class="text-[14px] text-slate">Providers respond with structured offers — line items, inclusions, and their own terms, side by side.</p>
            </div>
            <div class="mkt-reveal bg-surface-raised border border-line rounded-lg p-6" data-reveal-delay="180">
                <span class="block w-8 h-8 rounded-full bg-green-100 text-green-900 text-[13px] font-bold flex items-center justify-center mb-4">3</span>
                <p class="text-[15px] font-semibold text-ink mb-1">Agree between yourselves</p>
                <p class="text-[14px] text-slate">Accept an offer and you're connected directly. The agreement is between you and the provider — Event Go doesn't hold funds or mediate.</p>
            </div>
        </div>
    </div>

    <x-marketing.cta-band
        heading="Ready to get started"
        subcopy="Posting an event is free. So is registering as a provider."
    >
        <a href="{{ route('register', ['intent' => 'organiser']) }}"
           class="bg-white hover:-translate-y-0.5 hover:shadow-lg text-green-900 rounded-sm px-5 py-3 text-[14px] font-semibold transition-all duration-200">
            Plan an event
        </a>
        <a href="{{ route('register', ['intent' => 'provider']) }}"
           class="border border-white/25 hover:border-amber-500 hover:-translate-y-0.5 text-white rounded-sm px-5 py-3 text-[14px] font-semibold transition-all duration-200">
            Offer your services
        </a>
    </x-marketing.cta-band>
</x-layouts.marketing>
