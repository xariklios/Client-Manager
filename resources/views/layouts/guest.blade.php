<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased">

    <div class="min-h-screen flex">

        {{-- Left panel (decorative) --}}
        <div class="hidden lg:flex lg:w-1/2 bg-gray-950 flex-col items-center justify-center p-12">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500 flex items-center justify-center mb-6">
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">{{ config('app.name') }}</h1>
            <p class="text-gray-500 text-sm text-center max-w-xs">
                Manage your clients, projects, charges and offers in one place.
            </p>
        </div>

        {{-- Right panel (form) --}}
        <div class="flex-1 flex items-center justify-center bg-gray-50 px-6 py-12">
            {{ $slot }}
        </div>

    </div>

    @livewireScripts
</body>
</html>
