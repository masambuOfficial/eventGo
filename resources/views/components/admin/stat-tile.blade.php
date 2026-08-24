@props(['label', 'value', 'href' => null])

@if ($href)
    <a href="{{ $href }}" class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition-colors duration-200">
        <p class="text-[13px] text-slate mb-1">{{ $label }}</p>
        <p class="text-[28px] font-semibold text-ink tabular-nums">{{ $value }}</p>
    </a>
@else
    <div class="bg-surface-raised border border-line rounded-lg p-6">
        <p class="text-[13px] text-slate mb-1">{{ $label }}</p>
        <p class="text-[28px] font-semibold text-ink tabular-nums">{{ $value }}</p>
    </div>
@endif
