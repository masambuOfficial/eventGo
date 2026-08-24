<div class="max-w-lg mx-auto">
    <a href="{{ route('provider.dashboard') }}" class="text-[13px] text-slate hover:text-ink">&larr; Back to dashboard</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-1">Get verified</h1>
    <p class="text-[14px] text-slate mb-6">
        Link a business page you actively post on. A member of our team checks it — usually within a couple of
        days — and you'll get a Profile Verified badge, and can start submitting offers.
    </p>

    @if ($submitted)
        <div class="bg-surface-raised border border-line rounded-lg p-6">
            <p class="text-[14px] text-ink">
                Thanks — we've received your page details and will review them shortly.
            </p>
        </div>
    @else
        <form wire:submit="submit" class="bg-surface-raised border border-line rounded-lg p-6 space-y-4">
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Platform</label>
                <select wire:model="platform"
                        class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                    <option value="tiktok">TikTok</option>
                    <option value="x">X</option>
                    <option value="linkedin">LinkedIn</option>
                </select>
            </div>

            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Page handle <span class="text-amber-700">*</span></label>
                <input type="text" wire:model.blur="handle" placeholder="@yourbusiness"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                @error('handle') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Page link <span class="text-amber-700">*</span></label>
                <input type="text" wire:model.blur="profile_url" placeholder="https://facebook.com/yourbusiness"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                @error('profile_url') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Follower count (approximate)</label>
                <input type="number" wire:model.blur="follower_count"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                <span wire:loading.remove wire:target="submit">Submit for review</span>
                <span wire:loading wire:target="submit">Submitting…</span>
            </button>
        </form>
    @endif
</div>
