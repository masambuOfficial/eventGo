{{-- Must run before first paint to avoid a flash of the wrong theme.
     Dark is the default; only a "light" cookie (set once the user
     explicitly toggles) needs a synchronous override before Tailwind's
     CSS applies. --}}
<script>
    (function () {
        var match = document.cookie.match(/(?:^|; )eg_theme=([^;]*)/);
        var theme = match ? decodeURIComponent(match[1]) : 'dark';
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    })();
</script>
