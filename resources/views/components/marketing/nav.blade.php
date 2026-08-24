<nav class="sticky top-0 z-50 border-b border-line bg-surface-raised">
    <div class="max-w-6xl mx-auto px-4">
        <div class="h-16 flex items-center justify-between">
            <a href="{{ route('marketing.home') }}">
                <x-logo class="h-8 w-auto" />
            </a>

            <div class="hidden md:flex items-center gap-6 text-[14px]">
                <a href="{{ route('marketing.organisers') }}" class="text-slate hover:text-ink">For organisers</a>
                <a href="{{ route('marketing.providers') }}" class="text-slate hover:text-ink">For providers</a>
                <a href="{{ route('marketing.pricing') }}" class="text-slate hover:text-ink">Pricing</a>
            </div>

            <div class="hidden md:flex items-center gap-4 text-[14px]">
                <x-theme-toggle />
                @auth
                    <a href="{{ route('home') }}" class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 font-medium transition">
                        Go to dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-slate hover:text-ink">Log in</a>
                    <a href="{{ route('register') }}" class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 font-medium transition">
                        Sign up
                    </a>
                @endauth
            </div>

            <label for="marketing-nav-toggle" class="md:hidden text-ink cursor-pointer" aria-label="Open menu">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6" />
                    <line x1="3" y1="12" x2="21" y2="12" />
                    <line x1="3" y1="18" x2="21" y2="18" />
                </svg>
            </label>
        </div>

        <input type="checkbox" id="marketing-nav-toggle" class="peer hidden">

        <div class="hidden peer-checked:flex md:hidden flex-col gap-1 pb-4 text-[14px]">
            <a href="{{ route('marketing.organisers') }}" class="px-2 py-2 text-slate hover:text-ink">For organisers</a>
            <a href="{{ route('marketing.providers') }}" class="px-2 py-2 text-slate hover:text-ink">For providers</a>
            <a href="{{ route('marketing.pricing') }}" class="px-2 py-2 text-slate hover:text-ink">Pricing</a>
            <div class="px-2 py-2"><x-theme-toggle /></div>
            @auth
                <a href="{{ route('home') }}" class="mt-2 text-center bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 font-medium transition">
                    Go to dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="px-2 py-2 text-slate hover:text-ink">Log in</a>
                <a href="{{ route('register') }}" class="mt-2 text-center bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 font-medium transition">
                    Sign up
                </a>
            @endauth
        </div>
    </div>
</nav>
