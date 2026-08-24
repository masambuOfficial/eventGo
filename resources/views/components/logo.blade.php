@props(['class' => 'h-8 w-auto'])

{{-- Two logo files, CSS-swapped: eventGO_logo.svg sets the wordmark in dark
     ink (for the light surface), eventGO_logo_light.svg sets it in white
     (for the dark surface) — the file name names what it's drawn for, not
     the theme it's shown in. Same [data-theme="dark"] toggle pattern as
     theme-toggle.blade.php's sun/moon icons. --}}
<span class="eg-logo inline-block">
    <img src="{{ asset('eventGO_logo.svg') }}" alt="Event Go" class="eg-logo-for-light {{ $class }}">
    <img src="{{ asset('eventGO_logo_light.svg') }}" alt="Event Go" class="eg-logo-for-dark {{ $class }}">
</span>

<style>
    .eg-logo .eg-logo-for-dark { display: none; }
    :root[data-theme="dark"] .eg-logo .eg-logo-for-light { display: none; }
    :root[data-theme="dark"] .eg-logo .eg-logo-for-dark { display: inline-block; }
</style>
