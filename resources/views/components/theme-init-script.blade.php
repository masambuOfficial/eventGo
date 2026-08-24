{{-- Must run before first paint to avoid a flash of the wrong theme.
     Light is the default (no attribute needed); only a "dark" cookie
     needs a synchronous override before Tailwind's CSS applies. --}}
<script>
    (function () {
        var match = document.cookie.match(/(?:^|; )eg_theme=([^;]*)/);
        var theme = match ? decodeURIComponent(match[1]) : 'light';
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    })();
</script>
