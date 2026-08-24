<div class="max-w-lg mx-auto">
    <a href="{{ route('home') }}" class="text-[13px] text-slate hover:text-ink">&larr; Back</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-1">Security</h1>
    <p class="text-[14px] text-slate mb-6">
        Two-factor authentication adds a second step to sign-in, using an authenticator app on your phone
        (Google Authenticator, Authy, or similar). We don't use SMS codes.
    </p>

    <div class="bg-surface-raised border border-line rounded-lg p-6 space-y-4">
        @if ($this->showingRecoveryCodes)
            <div>
                <h2 class="text-[16px] font-semibold text-ink mb-1">Save your recovery codes</h2>
                <p class="text-[13px] text-slate mb-3">
                    Store these somewhere safe. Each code can be used once to sign in if you lose access to
                    your authenticator app. They won't be shown again.
                </p>
                <ul class="grid grid-cols-2 gap-2 font-mono text-[13px] text-ink bg-surface border border-line rounded-sm p-3 mb-4">
                    @foreach ($this->recoveryCodes as $recoveryCode)
                        <li>{{ $recoveryCode }}</li>
                    @endforeach
                </ul>
                <button type="button" wire:click="$set('showingRecoveryCodes', false)"
                        class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition">
                    I've saved these
                </button>
            </div>
        @elseif ($this->showingConfirmation)
            <div>
                <h2 class="text-[16px] font-semibold text-ink mb-3">Scan this QR code</h2>
                <div class="bg-white inline-block p-3 rounded-sm border border-line mb-4">
                    {!! $this->qrCodeSvg !!}
                </div>
                <p class="text-[13px] text-slate mb-4">
                    Scan with your authenticator app, then enter the 6-digit code it shows to confirm setup.
                </p>

                <form wire:submit="confirm" class="space-y-4">
                    <div>
                        <label for="code" class="block text-[14px] font-medium text-ink mb-1">Authentication code</label>
                        <input id="code" type="text" inputmode="numeric" autocomplete="one-time-code" wire:model="code" autofocus
                               class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink tracking-widest focus:outline-none focus:ring-2 focus:ring-green-600">
                        @error('code') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" wire:loading.attr="disabled" wire:target="confirm"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                            <span wire:loading.remove wire:target="confirm">Confirm</span>
                            <span wire:loading wire:target="confirm">Confirming…</span>
                        </button>
                        <button type="button" wire:click="disable" wire:loading.attr="disabled" wire:target="disable"
                                class="flex-1 border border-line text-ink rounded-sm px-4 py-2 text-[14px] font-medium hover:bg-surface transition disabled:opacity-60">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        @elseif ($this->enabled)
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[14px] font-medium text-ink">Two-factor authentication is on</p>
                    <p class="text-[13px] text-slate">Your account is protected with an authenticator app.</p>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" wire:click="regenerateRecoveryCodes" wire:loading.attr="disabled" wire:target="regenerateRecoveryCodes"
                        class="flex-1 border border-line text-ink rounded-sm px-4 py-2 text-[14px] font-medium hover:bg-surface transition disabled:opacity-60">
                    Regenerate recovery codes
                </button>
                <button type="button" wire:click="disable" wire:loading.attr="disabled" wire:target="disable"
                        wire:confirm="Turn off two-factor authentication?"
                        class="flex-1 border border-line text-amber-700 rounded-sm px-4 py-2 text-[14px] font-medium hover:bg-surface transition disabled:opacity-60">
                    Disable
                </button>
            </div>
        @else
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[14px] font-medium text-ink">Two-factor authentication is off</p>
                    <p class="text-[13px] text-slate">Turn it on for an extra layer of protection at sign-in.</p>
                </div>
            </div>

            <button type="button" wire:click="enable" wire:loading.attr="disabled" wire:target="enable"
                    class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                <span wire:loading.remove wire:target="enable">Enable two-factor authentication</span>
                <span wire:loading wire:target="enable">Setting up…</span>
            </button>
        @endif
    </div>
</div>
