<x-layouts.marketing title="For providers — Event Go" description="Get qualified opportunities from organisers actively planning events near you.">
    <x-marketing.hero>
        <x-slot:heading>Get in front of organisers who are ready to book</x-slot:heading>
        <x-slot:subcopy>
            Set up your profile once, list what you offer and where, and get matched to opportunities
            with a real guest count, date, district and budget — not "are you available?" messages.
        </x-slot:subcopy>
        <x-slot:cta>
            <a href="{{ route('register', ['intent' => 'provider']) }}"
               class="bg-green-600 hover:bg-green-700 hover:-translate-y-0.5 hover:shadow-[0_12px_28px_-8px_rgba(46,156,130,.6)] text-white rounded-sm px-5 py-3 text-[14px] font-semibold transition-all duration-200">
                Offer your services
            </a>
        </x-slot:cta>
    </x-marketing.hero>

    <x-marketing.category-grid
        heading="What do you offer"
        :items="$categories"
        intent="provider"
        :show-children="true"
    />

    <div class="bg-surface-raised border-y border-line">
        <div class="max-w-6xl mx-auto px-4 py-16 md:py-20">
            <h2 class="mkt-reveal text-[26px] md:text-[30px] font-bold text-ink tracking-tight mb-10">How opportunities reach you</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="mkt-reveal bg-surface border border-line rounded-lg p-6 transition-all duration-300 hover:-translate-y-1 hover:border-green-600" data-reveal-delay="0">
                    <span class="block w-8 h-8 rounded-full bg-green-100 text-green-900 text-[13px] font-bold flex items-center justify-center mb-4">1</span>
                    <p class="text-[15px] font-semibold text-ink mb-1">Matched automatically</p>
                    <p class="text-[14px] text-slate">Requirements in your categories and service areas are matched to your profile.</p>
                </div>
                <div class="mkt-reveal bg-surface border border-line rounded-lg p-6 transition-all duration-300 hover:-translate-y-1 hover:border-green-600" data-reveal-delay="90">
                    <span class="block w-8 h-8 rounded-full bg-green-100 text-green-900 text-[13px] font-bold flex items-center justify-center mb-4">2</span>
                    <p class="text-[15px] font-semibold text-ink mb-1">Or invited directly</p>
                    <p class="text-[14px] text-slate">Organisers can invite you by name to a specific requirement.</p>
                </div>
                <div class="mkt-reveal bg-surface border border-line rounded-lg p-6 transition-all duration-300 hover:-translate-y-1 hover:border-green-600" data-reveal-delay="180">
                    <span class="block w-8 h-8 rounded-full bg-green-100 text-green-900 text-[13px] font-bold flex items-center justify-center mb-4">3</span>
                    <p class="text-[15px] font-semibold text-ink mb-1">Send a structured offer</p>
                    <p class="text-[14px] text-slate">Line items, your inclusions and exclusions, and your own terms — presented clearly against every other offer the organiser sees.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-16 md:py-20">
        <div class="mkt-reveal bg-green-50 border border-line rounded-lg p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <h2 class="text-[20px] font-bold text-ink tracking-tight mb-2">Free to start, plans for more visibility</h2>
                <p class="text-[14px] text-slate max-w-md">
                    Registration and a free profile cost nothing. Paid plans raise your offer limit and
                    add featured placement — see the real prices before you commit to anything.
                </p>
            </div>
            <a href="{{ route('marketing.pricing') }}" class="text-[14px] font-semibold text-green-600 hover:underline whitespace-nowrap">
                See pricing &rarr;
            </a>
        </div>
    </div>

    <x-marketing.cta-band
        heading="Set up your profile"
        subcopy="It's free, and Event Go never takes a cut of what you agree with an organiser."
    >
        <a href="{{ route('register', ['intent' => 'provider']) }}"
           class="bg-white hover:-translate-y-0.5 hover:shadow-lg text-green-900 rounded-sm px-5 py-3 text-[14px] font-semibold transition-all duration-200">
            Offer your services
        </a>
    </x-marketing.cta-band>
</x-layouts.marketing>
