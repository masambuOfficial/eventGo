@props(['class' => 'text-slate hover:text-ink'])

{{-- Class-based, not ID-based: this component may appear more than once
     on the same page (e.g. desktop nav + mobile menu), so every instance
     needs to bind and stay in sync rather than relying on a unique id. --}}
<button type="button" class="theme-toggle {{ $class }} inline-flex items-center" aria-label="Switch between dark and light">
    <svg class="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="4" />
        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
    </svg>
    <svg class="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
    </svg>
</button>

<style>
    .theme-toggle .theme-icon-moon { display: none; }
    :root[data-theme="dark"] .theme-toggle .theme-icon-sun { display: none; }
    :root[data-theme="dark"] .theme-toggle .theme-icon-moon { display: block; }
</style>

<script>
    (function () {
        function bind(btn) {
            if (btn.dataset.bound) return;
            btn.dataset.bound = '1';
            btn.addEventListener('click', function () {
                var root = document.documentElement;
                var goingDark = root.getAttribute('data-theme') !== 'dark';
                if (goingDark) {
                    root.setAttribute('data-theme', 'dark');
                    document.cookie = 'eg_theme=dark; path=/; max-age=31536000; SameSite=Lax';
                } else {
                    root.removeAttribute('data-theme');
                    document.cookie = 'eg_theme=light; path=/; max-age=31536000; SameSite=Lax';
                }
            });
        }
        document.querySelectorAll('.theme-toggle').forEach(bind);
    })();
</script>
