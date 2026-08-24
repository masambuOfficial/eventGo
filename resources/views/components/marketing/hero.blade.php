<div class="mkt-band relative overflow-hidden"
     style="clip-path: polygon(0 0, 100% 0, 100% 94%, 0 100%);">
    <div class="mkt-mesh" aria-hidden="true"></div>
    <div class="mkt-dotgrid" aria-hidden="true"></div>

    <div class="max-w-6xl mx-auto px-4 py-20 md:py-28 grid grid-cols-1 md:grid-cols-2 gap-12 items-center relative z-10">
        <div class="mkt-reveal">
            <span class="inline-flex items-center gap-2 text-[12.5px] font-semibold uppercase tracking-wide text-amber-500 bg-white/10 px-3 py-1.5 rounded-full mb-6">
                No commission, ever
            </span>
            <h1 class="text-[36px] md:text-[48px] font-bold text-white leading-[1.08] tracking-tight mb-5">
                {{ $heading }}
            </h1>
            <p class="text-[17px] text-white/75 leading-relaxed mb-8 max-w-md">
                {{ $subcopy }}
            </p>
            <div class="flex flex-wrap gap-3">
                {{ $cta }}
            </div>
        </div>

        @isset($visual)
            <div class="mkt-reveal" data-reveal-delay="120">
                {{ $visual }}
            </div>
        @endisset
    </div>
</div>
