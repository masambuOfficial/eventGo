@props(['heading', 'items', 'intent', 'showChildren' => false])

<div class="max-w-6xl mx-auto px-4 py-16 md:py-20">
    <div class="text-center max-w-xl mx-auto mb-10">
        <h2 class="mkt-reveal text-[26px] md:text-[30px] font-bold text-ink tracking-tight mb-2">{{ $heading }}</h2>
        <p class="mkt-reveal text-[15px] text-slate">Real categories, matched to providers who actually cover them.</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        @foreach ($items as $index => $item)
            <a href="{{ route('register', ['intent' => $intent]) }}"
               data-reveal-delay="{{ $index * 55 }}"
               class="mkt-reveal group block bg-surface-raised border border-line rounded-lg p-5 transition-all duration-300 hover:-translate-y-1.5 hover:border-green-600 hover:shadow-[0_18px_36px_-18px_rgba(20,112,92,.45)]">
                <span class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100 text-green-700 mb-4 transition-colors duration-300 group-hover:bg-green-600 group-hover:text-white">
                    <x-icon :name="$item->icon" class="w-4 h-4" />
                </span>
                <p class="text-[14.5px] font-semibold text-ink">{{ $item->name }}</p>
                @if ($showChildren && $item->children->isNotEmpty())
                    <p class="mt-1 text-[12px] text-slate">
                        {{ $item->children->pluck('name')->take(3)->join(', ') }}{{ $item->children->count() > 3 ? '…' : '' }}
                    </p>
                @endif
            </a>
        @endforeach
    </div>
</div>
