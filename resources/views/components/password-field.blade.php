@props([
    'name' => 'password',
    'id' => null,
    'label' => 'Password',
    'required' => true,
    'autofocus' => false,
    'autocomplete' => null,
])

@php $fieldId = $id ?? $name; @endphp

<div>
    <label for="{{ $fieldId }}" class="block text-[14px] font-medium text-ink mb-1">{{ $label }}</label>
    <div class="relative">
        <input id="{{ $fieldId }}" type="password" name="{{ $name }}"
               @required($required) @if ($autofocus) autofocus @endif
               @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
               class="w-full border border-line rounded-sm pl-3 pr-10 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
        <button type="button" class="password-toggle absolute inset-y-0 right-0 flex items-center px-3 text-slate hover:text-ink" aria-label="Show password" aria-pressed="false" tabindex="-1">
            <svg class="password-icon-eye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
            </svg>
            <svg class="password-icon-eye-off hidden" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.6 21.6 0 015.06-6.06M9.9 4.24A9.13 9.13 0 0112 4c7 0 11 8 11 8a21.6 21.6 0 01-2.16 3.19M14.12 14.12a3 3 0 11-4.24-4.24" />
                <path d="M1 1l22 22" />
            </svg>
        </button>
    </div>
    @error($name)
        <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p>
    @enderror
</div>

{{-- Class-based, not ID-based: a page can render more than one password
     field (e.g. password + confirmation), so every toggle needs to bind
     independently rather than relying on a unique id. --}}
<script>
    (function () {
        function bind(btn) {
            if (btn.dataset.bound) return;
            btn.dataset.bound = '1';
            var input = btn.parentElement.querySelector('input');
            var eye = btn.querySelector('.password-icon-eye');
            var eyeOff = btn.querySelector('.password-icon-eye-off');
            btn.addEventListener('click', function () {
                var willShow = input.type === 'password';
                input.type = willShow ? 'text' : 'password';
                btn.setAttribute('aria-pressed', String(willShow));
                btn.setAttribute('aria-label', willShow ? 'Hide password' : 'Show password');
                eye.classList.toggle('hidden', willShow);
                eyeOff.classList.toggle('hidden', !willShow);
            });
        }
        document.querySelectorAll('.password-toggle').forEach(bind);
    })();
</script>
