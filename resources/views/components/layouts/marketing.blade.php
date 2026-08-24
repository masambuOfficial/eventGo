<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-theme-init-script />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Event Go' }}</title>
    <meta name="description" content="{{ $description ?? 'Turn your event into a costed plan and source real providers against it. No commission, ever.' }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-ink font-sans antialiased min-h-screen flex flex-col">
    <x-marketing.nav />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-marketing.footer />
</body>
</html>
