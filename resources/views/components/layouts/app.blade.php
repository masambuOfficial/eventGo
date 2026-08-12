<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Event Go') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-surface text-ink font-sans antialiased min-h-screen">
    <nav class="border-b border-line bg-white">
        <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ url('/home') }}" class="text-[16px] font-semibold text-ink">
                event<span class="text-green-600">GO</span>
            </a>

            <div class="flex items-center gap-4 text-[14px]">
                @auth
                    @role('admin')
                        <a href="{{ route('admin.dashboard') }}" class="text-slate hover:text-ink">Admin</a>
                    @endrole
                    <span class="text-slate">{{ auth()->user()->full_name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Log out" aria-label="Log out" class="text-slate hover:text-ink">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" y1="12" x2="9" y2="12" />
                            </svg>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
