<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-theme-init-script />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Event Go') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-ink font-sans antialiased min-h-screen flex flex-col items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">
        <div class="mb-8 flex items-center justify-center gap-4">
            <a href="{{ url('/') }}" class="inline-block">
                <x-logo class="h-8 w-auto" />
            </a>
            <x-theme-toggle />
        </div>

        <div class="bg-surface-raised border border-line rounded-lg shadow-sm p-6">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
