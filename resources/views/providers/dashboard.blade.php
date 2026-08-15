<x-layouts.app title="Provider dashboard">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-[24px] font-semibold text-ink">{{ $provider->business_name }}</h1>
                <p class="text-[14px] text-slate">{{ $provider->baseDistrict?->name }}</p>
            </div>

            @if ($provider->verification_tier === 1)
                <span class="bg-green-100 text-green-900 text-[13px] font-medium px-3 py-1 rounded-full">Profile Verified</span>
            @elseif ($provider->verification_tier === 2)
                <span class="bg-green-600 text-white text-[13px] font-medium px-3 py-1 rounded-full">Business Verified</span>
            @elseif ($provider->verification_tier >= 3)
                <span class="bg-green-900 text-amber-500 text-[13px] font-medium px-3 py-1 rounded-full">Event Go Verified</span>
            @endif
        </div>

        <div class="bg-white border border-line rounded-lg p-6 mb-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[14px] font-medium text-ink">Profile completeness</p>
                <p class="text-[14px] text-slate">{{ $provider->profile_completeness }}%</p>
            </div>
            <div class="h-2 bg-line rounded-full overflow-hidden">
                <div class="h-full bg-green-600" style="width: {{ $provider->profile_completeness }}%"></div>
            </div>
            <a href="{{ route('provider.onboarding') }}" class="inline-block mt-3 text-[13px] text-green-600 hover:underline">
                Edit business info, services & areas
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white border border-line rounded-lg p-6">
                <h2 class="text-[18px] font-semibold text-ink mb-3">Services</h2>
                @forelse ($provider->services as $service)
                    <div class="flex items-center justify-between py-2 border-b border-line last:border-0">
                        <span class="text-[14px] text-ink">{{ $service->category->name }}</span>
                        @if ($service->price_min_ugx)
                            <span class="text-[13px] text-slate">
                                UGX {{ number_format($service->price_min_ugx) }}@if($service->price_max_ugx) – {{ number_format($service->price_max_ugx) }}@endif
                                @if ($service->price_unit) {{ $service->price_unit }} @endif
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="text-[14px] text-slate">No services added yet.</p>
                @endforelse
            </div>

            <div class="bg-white border border-line rounded-lg p-6">
                <h2 class="text-[18px] font-semibold text-ink mb-3">Areas served</h2>
                @if ($provider->serviceAreas->isNotEmpty())
                    <p class="text-[14px] text-ink">{{ $provider->serviceAreas->pluck('name')->join(', ') }}</p>
                @else
                    <p class="text-[14px] text-slate">No areas added yet.</p>
                @endif
            </div>

            <div class="bg-white border border-line rounded-lg p-6">
                <h2 class="text-[18px] font-semibold text-ink mb-1">Get verified</h2>
                <p class="text-[14px] text-slate mb-3">
                    Link your Facebook, Instagram or TikTok business page for a free Profile Verified badge.
                </p>
                @if ($provider->socialAccounts->isNotEmpty())
                    @foreach ($provider->socialAccounts as $account)
                        <p class="text-[14px] text-ink">
                            {{ ucfirst($account->platform) }} — {{ $account->handle }}
                            @if ($account->verified_at)
                                <span class="text-green-600 text-[13px]">Verified</span>
                            @else
                                <span class="text-amber-700 text-[13px]">Pending review</span>
                            @endif
                        </p>
                    @endforeach
                @else
                    <a href="{{ route('provider.verification.create') }}" class="text-[13px] text-green-600 hover:underline">Link a social page</a>
                @endif
            </div>

            <div class="bg-white border border-line rounded-lg p-6">
                <h2 class="text-[18px] font-semibold text-ink mb-1">Portfolio</h2>
                <p class="text-[14px] text-slate">
                    {{ $provider->media->count() }} photo(s) uploaded.
                    <a href="{{ route('provider.media.index') }}" class="text-green-600 hover:underline">Manage portfolio</a>
                </p>
            </div>

            <div class="bg-white border border-line rounded-lg p-6">
                <h2 class="text-[18px] font-semibold text-ink mb-1">Availability</h2>
                <p class="text-[14px] text-slate">
                    <a href="{{ route('provider.availability.index') }}" class="text-green-600 hover:underline">Manage your calendar</a>
                </p>
            </div>

            <div class="bg-white border border-line rounded-lg p-6">
                <h2 class="text-[18px] font-semibold text-ink mb-1">Opportunities</h2>
                <p class="text-[14px] text-slate">
                    <a href="{{ route('provider.opportunities.index') }}" class="text-green-600 hover:underline">Browse open requirements</a>
                </p>
            </div>

            <div class="bg-white border border-line rounded-lg p-6">
                <h2 class="text-[18px] font-semibold text-ink mb-1">Invitations</h2>
                <p class="text-[14px] text-slate">
                    <a href="{{ route('provider.invitations.index') }}" class="text-green-600 hover:underline">See who invited you directly</a>
                </p>
            </div>

            <div class="bg-white border border-line rounded-lg p-6">
                <h2 class="text-[18px] font-semibold text-ink mb-1">Your offers</h2>
                <p class="text-[14px] text-slate">
                    <a href="{{ route('provider.offers.index') }}" class="text-green-600 hover:underline">Track what you've submitted</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
