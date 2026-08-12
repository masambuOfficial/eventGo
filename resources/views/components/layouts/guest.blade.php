<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Event Go') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-ink font-sans antialiased min-h-screen flex flex-col items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">
        <div class="mb-8 text-center">
            <a href="{{ url('/') }}" class="text-xl font-semibold text-ink">
                event<span class="text-green-600">GO</span>
            </a>
        </div>

        <div class="bg-white border border-line rounded-lg shadow-sm p-6">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
