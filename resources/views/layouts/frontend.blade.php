<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            .bg-dots-darker { background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E"); }
            .bg-dots-lighter { background-image: none; }
            @media (prefers-color-scheme: dark) {
                .bg-dots-darker { background-image: none; }
                .bg-dots-lighter { background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E"); }
            }
        </style>
    </head>
    <body class="antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <div class="min-h-screen flex flex-col">
            {{-- Header with menu --}}
            <header class="bg-white dark:bg-gray-800 shadow border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center gap-8">
                            <a href="{{ route('home') }}" class="flex items-center shrink-0 font-semibold text-gray-800 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                <x-application-mark class="block h-9 w-auto" />
                            </a>
                            <nav class="hidden sm:flex items-center gap-1">
                                <a href="{{ route('home') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('home') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white' }}">
                                    {{ __('Home') }}
                                </a>
                                @isset($menuPages)
                                    @foreach ($menuPages as $menuPage)
                                        <a href="{{ route('page.show', $menuPage->slug) }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('page.show') && request()->route('slug') === $menuPage->slug ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white' }}">
                                            {{ $menuPage->getTitle($langCode ?? null) ?: $menuPage->slug }}
                                        </a>
                                    @endforeach
                                @endisset
                            </nav>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ url('/admin/dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white">
                                        {{ __('Dashboard') }}
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white">
                                        {{ __('Log in') }}
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="px-3 py-2 rounded-md text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700">
                                            {{ __('Register') }}
                                        </a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>
                    {{-- Mobile menu --}}
                    <div class="sm:hidden pb-3">
                        <div class="flex flex-wrap gap-1">
                            <a href="{{ route('home') }}" class="px-3 py-1.5 rounded text-sm font-medium {{ request()->routeIs('home') ? 'bg-gray-100 dark:bg-gray-700' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                {{ __('Home') }}
                            </a>
                            @isset($menuPages)
                                @foreach ($menuPages as $menuPage)
                                    <a href="{{ route('page.show', $menuPage->slug) }}" class="px-3 py-1.5 rounded text-sm font-medium {{ request()->routeIs('page.show') && request()->route('slug') === $menuPage->slug ? 'bg-gray-100 dark:bg-gray-700' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                        {{ $menuPage->getTitle($langCode ?? null) ?: $menuPage->slug }}
                                    </a>
                                @endforeach
                            @endisset
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1">
                @yield('content')
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
