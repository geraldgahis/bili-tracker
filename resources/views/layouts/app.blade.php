<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'PesoScan') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- THEME SCRIPT: runs on first load AND on every wire:navigate swap -->
    <script>
        function applyStoredTheme() {
            const isDark = localStorage.getItem('darkMode') === 'true' ||
                (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', isDark);
        }

        // Run immediately to prevent flash on first load
        applyStoredTheme();

        // Re-run after every Livewire soft navigation, since wire:navigate
        // morphs <html> attributes back to the server-rendered markup
        // (which never has the dark class), silently wiping it otherwise.
        document.addEventListener('livewire:navigated', applyStoredTheme);
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="document.documentElement.classList.toggle('dark', darkMode);
$watch('darkMode', val => {
    document.documentElement.classList.toggle('dark', val);
    localStorage.setItem('darkMode', val);
});"
    class="antialiased bg-slate-50 dark:bg-bg-dark text-slate-800 dark:text-[#F4F4F5] transition-colors duration-200">

    {{-- @auth
        <livewire:navbar />
    @endauth --}}

    @unless (request()->routeIs('login', 'register'))
        <livewire:navbar />
    @endunless

    {{ $slot }}
</body>

</html>
