<x-layouts.guest title="Two-factor verification">
    <h1 class="text-[24px] font-semibold text-ink mb-2">Two-factor verification</h1>
    <p id="two-factor-code-hint" class="text-[14px] text-slate mb-6">
        Enter the 6-digit code from your authenticator app.
    </p>
    <p id="two-factor-recovery-hint" class="text-[14px] text-slate mb-6 hidden">
        Enter one of your recovery codes.
    </p>

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-4">
        @csrf

        <div id="two-factor-code-field">
            <label for="code" class="block text-[14px] font-medium text-ink mb-1">Authentication code</label>
            <input id="code" type="text" inputmode="numeric" autocomplete="one-time-code" name="code" autofocus
                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink tracking-widest focus:outline-none focus:ring-2 focus:ring-green-600">
            @error('code')
                <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p>
            @enderror
        </div>

        <div id="two-factor-recovery-field" class="hidden">
            <label for="recovery_code" class="block text-[14px] font-medium text-ink mb-1">Recovery code</label>
            <input id="recovery_code" type="text" autocomplete="one-time-code" name="recovery_code"
                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            @error('recovery_code')
                <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition">
            Verify
        </button>

        <button type="button" id="two-factor-mode-toggle"
                class="w-full text-center text-[13px] text-green-600 hover:underline">
            Use a recovery code instead
        </button>
    </form>

    <script>
        (function () {
            var toggle = document.getElementById('two-factor-mode-toggle');
            var codeField = document.getElementById('two-factor-code-field');
            var recoveryField = document.getElementById('two-factor-recovery-field');
            var codeHint = document.getElementById('two-factor-code-hint');
            var recoveryHint = document.getElementById('two-factor-recovery-hint');
            var codeInput = document.getElementById('code');
            var recoveryInput = document.getElementById('recovery_code');
            var usingRecovery = false;

            toggle.addEventListener('click', function () {
                usingRecovery = !usingRecovery;
                codeField.classList.toggle('hidden', usingRecovery);
                recoveryField.classList.toggle('hidden', !usingRecovery);
                codeHint.classList.toggle('hidden', usingRecovery);
                recoveryHint.classList.toggle('hidden', !usingRecovery);
                codeInput.required = !usingRecovery;
                recoveryInput.required = usingRecovery;
                toggle.textContent = usingRecovery ? 'Use an authentication code instead' : 'Use a recovery code instead';
                (usingRecovery ? recoveryInput : codeInput).focus();
            });
        })();
    </script>
</x-layouts.guest>
