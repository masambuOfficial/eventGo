<div class="mkt-band relative overflow-hidden">
    <div class="mkt-mesh" aria-hidden="true"></div>
    <div class="max-w-6xl mx-auto px-4 py-20 text-center relative z-10">
        <h2 class="mkt-reveal text-[26px] md:text-[32px] font-bold text-white tracking-tight mb-3">{{ $heading }}</h2>
        <p class="mkt-reveal text-[15px] text-white/70 mb-9 max-w-lg mx-auto">{{ $subcopy }}</p>
        <div class="mkt-reveal flex flex-wrap gap-3 justify-center" data-reveal-delay="100">
            {{ $slot }}
        </div>
    </div>
</div>
