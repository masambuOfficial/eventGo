<x-layouts.marketing title="For organisers — Event Go" description="Turn your event into a costed requirements list and source real providers against it.">
    <x-marketing.hero>
        <x-slot:heading>Plan your event like a professional does</x-slot:heading>
        <x-slot:subcopy>
            A wedding, a graduation party, a conference — tell us the basics and get a structured plan
            with real quantities and a budget estimate, ready to send out to providers.
        </x-slot:subcopy>
        <x-slot:cta>
            <a href="{{ route('register', ['intent' => 'organiser']) }}"
               class="bg-green-600 hover:bg-green-700 hover:-translate-y-0.5 hover:shadow-[0_12px_28px_-8px_rgba(46,156,130,.6)] text-white rounded-sm px-5 py-3 text-[14px] font-semibold transition-all duration-200">
                Plan an event
            </a>
        </x-slot:cta>
    </x-marketing.hero>

    <div class="max-w-6xl mx-auto px-4 py-16 md:py-20">
        <h2 class="mkt-reveal text-[26px] md:text-[30px] font-bold text-ink tracking-tight mb-10">From a vague idea to a costed plan</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="mkt-reveal bg-surface-raised border border-line rounded-lg p-6 transition-all duration-300 hover:-translate-y-1 hover:border-green-600" data-reveal-delay="0">
                <span class="block w-8 h-8 rounded-full bg-green-100 text-green-900 text-[13px] font-bold flex items-center justify-center mb-4">1</span>
                <p class="text-[15px] font-semibold text-ink mb-1">Answer a few questions</p>
                <p class="text-[14px] text-slate">Guest count, venue, district, and a handful of details specific to your event type.</p>
            </div>
            <div class="mkt-reveal bg-surface-raised border border-line rounded-lg p-6 transition-all duration-300 hover:-translate-y-1 hover:border-green-600" data-reveal-delay="90">
                <span class="block w-8 h-8 rounded-full bg-green-100 text-green-900 text-[13px] font-bold flex items-center justify-center mb-4">2</span>
                <p class="text-[15px] font-semibold text-ink mb-1">Get a generated requirements list</p>
                <p class="text-[14px] text-slate">Catering, seating, tents, ushers and more — quantities and a budget estimate for each line, all editable before you commit.</p>
            </div>
            <div class="mkt-reveal bg-surface-raised border border-line rounded-lg p-6 transition-all duration-300 hover:-translate-y-1 hover:border-green-600" data-reveal-delay="180">
                <span class="block w-8 h-8 rounded-full bg-green-100 text-green-900 text-[13px] font-bold flex items-center justify-center mb-4">3</span>
                <p class="text-[15px] font-semibold text-ink mb-1">Source and compare</p>
                <p class="text-[14px] text-slate">Publish an open opportunity or invite providers directly. Compare structured offers — line items, inclusions, exclusions, and each provider's own terms — side by side.</p>
            </div>
        </div>
    </div>

    <div class="bg-surface-raised border-y border-line">
        <x-marketing.category-grid
            heading="What kind of event are you planning"
            :items="$eventTypes"
            intent="organiser"
        />
    </div>

    <div class="max-w-6xl mx-auto px-4 py-16 md:py-20">
        <h2 class="mkt-reveal text-[26px] md:text-[30px] font-bold text-ink tracking-tight mb-10">What you get</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="mkt-reveal bg-surface-raised border border-line rounded-lg p-6" data-reveal-delay="0">
                <p class="text-[15px] font-semibold text-ink mb-1">Structured offers, not guesswork</p>
                <p class="text-[14px] text-slate">Every offer carries its own line items, inclusions and exclusions, so a lower headline price doesn't hide missing scope.</p>
            </div>
            <div class="mkt-reveal bg-surface-raised border border-line rounded-lg p-6" data-reveal-delay="70">
                <p class="text-[15px] font-semibold text-ink mb-1">A shared workspace once you book</p>
                <p class="text-[14px] text-slate">A checklist, files, a message thread, and a record of any changes you agree to along the way.</p>
            </div>
            <div class="mkt-reveal bg-surface-raised border border-line rounded-lg p-6" data-reveal-delay="140">
                <p class="text-[15px] font-semibold text-ink mb-1">Direct agreements</p>
                <p class="text-[14px] text-slate">Once you accept an offer, contact details are shared immediately. The agreement is between you and the provider.</p>
            </div>
            <div class="mkt-reveal bg-surface-raised border border-line rounded-lg p-6" data-reveal-delay="210">
                <p class="text-[15px] font-semibold text-ink mb-1">Free to post</p>
                <p class="text-[14px] text-slate">Posting an event and requesting offers costs nothing. Event Go takes no commission on what you agree with a provider.</p>
            </div>
        </div>
    </div>

    <x-marketing.cta-band
        heading="Start with your event"
        subcopy="It takes a few minutes to get a costed plan."
    >
        <a href="{{ route('register', ['intent' => 'organiser']) }}"
           class="bg-white hover:-translate-y-0.5 hover:shadow-lg text-green-900 rounded-sm px-5 py-3 text-[14px] font-semibold transition-all duration-200">
            Plan an event
        </a>
    </x-marketing.cta-band>
</x-layouts.marketing>
