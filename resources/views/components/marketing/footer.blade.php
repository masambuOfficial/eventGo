<footer class="border-t border-line bg-surface-raised">
    <div class="max-w-6xl mx-auto px-4 py-12 grid grid-cols-1 sm:grid-cols-3 gap-8">
        <div>
            <a href="{{ route('marketing.home') }}">
                <x-logo class="h-8 w-auto" />
            </a>
            <p class="mt-2 text-[13px] text-slate max-w-xs">
                A neutral platform that connects organisers and event service providers in Uganda.
                No commission, ever — Event Go is never a party to the deal.
            </p>
        </div>

        <div>
            <p class="text-[13px] font-medium text-ink mb-3">Event Go</p>
            <ul class="space-y-2 text-[13px]">
                <li><a href="{{ route('marketing.home') }}" class="text-slate hover:text-ink">Home</a></li>
                <li><a href="{{ route('marketing.organisers') }}" class="text-slate hover:text-ink">For organisers</a></li>
                <li><a href="{{ route('marketing.providers') }}" class="text-slate hover:text-ink">For providers</a></li>
                <li><a href="{{ route('marketing.pricing') }}" class="text-slate hover:text-ink">Pricing</a></li>
            </ul>
        </div>

        <div>
            <p class="text-[13px] font-medium text-ink mb-3">Legal</p>
            <ul class="space-y-2 text-[13px]">
                <li><a href="{{ route('legal.privacy') }}" class="text-slate hover:text-ink">Privacy</a></li>
                <li><a href="{{ route('legal.terms') }}" class="text-slate hover:text-ink">Terms</a></li>
            </ul>
        </div>
    </div>

    <div class="border-t border-line">
        <div class="max-w-6xl mx-auto px-4 py-4">
            <p class="text-[12px] text-slate">&copy; {{ date('Y') }} Event Go. All rights reserved.</p>
        </div>
    </div>
</footer>
