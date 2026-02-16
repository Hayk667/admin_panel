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
    <body class="antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 overflow-x-hidden">
        <div class="min-h-screen flex flex-col">
            {{-- Header with menu --}}
            <header class="bg-white dark:bg-gray-800 shadow border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center gap-4 sm:gap-8">
                            <a href="{{ route('home') }}" class="flex items-center shrink-0 font-semibold text-gray-800 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                <x-application-mark class="block h-9 w-auto" />
                            </a>
                            {{-- Burger button (mobile only) --}}
                            <button type="button" id="burger-btn" class="sm:hidden p-2 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" aria-expanded="false" aria-controls="mobile-menu" aria-label="{{ __('Open menu') }}">
                                <svg id="burger-icon" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                                <svg id="burger-close-icon" class="w-6 h-6 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <nav class="hidden sm:flex items-center gap-1">
                                <a href="{{ route('home') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('home') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white' }}">
                                    {{ __('Home') }}
                                </a>
                                @isset($menuPages)
                                    @foreach ($menuPages as $menuPage)
                                        @if ($menuPage->children->isNotEmpty())
                                            <div class="relative group">
                                                <button type="button" class="px-3 py-2 rounded-md text-sm font-medium inline-flex items-center gap-0.5 {{ request()->routeIs('page.show') && in_array(request()->route('slug'), $menuPage->children->pluck('slug')->push($menuPage->slug)->toArray()) ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white' }}">
                                                    {{ $menuPage->getTitle($langCode ?? null) ?: $menuPage->slug }}
                                                    <svg class="w-4 h-4 ml-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                                </button>
                                                <div class="absolute left-0 top-full pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                                    <div class="py-1 bg-white dark:bg-gray-700 rounded-md shadow-lg border border-gray-200 dark:border-gray-600 min-w-[10rem]">
                                                        <a href="{{ route('page.show', $menuPage->slug) }}" class="block px-4 py-2 text-sm {{ (request()->route('slug') ?? '') === $menuPage->slug ? 'bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                                                            {{ $menuPage->getTitle($langCode ?? null) ?: $menuPage->slug }}
                                                        </a>
                                                        @foreach ($menuPage->children as $child)
                                                            <a href="{{ route('page.show', $child->slug) }}" class="block px-4 py-2 text-sm {{ (request()->route('slug') ?? '') === $child->slug ? 'bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                                                                {{ $child->getTitle($langCode ?? null) ?: $child->slug }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <a href="{{ route('page.show', $menuPage->slug) }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('page.show') && request()->route('slug') === $menuPage->slug ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white' }}">
                                                {{ $menuPage->getTitle($langCode ?? null) ?: $menuPage->slug }}
                                            </a>
                                        @endif
                                    @endforeach
                                @endisset
                            </nav>
                        </div>
                        <div class="flex items-center gap-2 max-sm:min-w-0">
                            <form action="{{ route('search') }}" method="get" class="hidden sm:flex items-center gap-1">
                                <label for="search-q" class="sr-only">{{ __('Search posts') }}</label>
                                <input type="search" name="q" id="search-q" value="{{ request('q') }}"
                                    placeholder="{{ __('Search posts…') }}"
                                    class="w-36 sm:w-44 px-2 py-1.5 rounded-md text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                <button type="submit" class="p-1.5 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200" aria-label="{{ __('Search') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                </button>
                            </form>
                            @if (Route::has('login'))
                                <div class="hidden sm:flex items-center gap-2">
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
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- Mobile menu (burger panel) --}}
                    <div id="mobile-menu" class="sm:hidden hidden border-t border-gray-200 dark:border-gray-700" aria-hidden="true">
                        <div class="py-3 space-y-3">
                            <form action="{{ route('search') }}" method="get" class="flex gap-1">
                                <label for="search-q-mobile" class="sr-only">{{ __('Search posts') }}</label>
                                <input type="search" name="q" id="search-q-mobile" value="{{ request('q') }}"
                                    placeholder="{{ __('Search posts…') }}"
                                    class="flex-1 min-w-0 px-2 py-1.5 rounded-md text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                <button type="submit" class="px-3 py-1.5 rounded-md text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                    {{ __('Search') }}
                                </button>
                            </form>
                            <nav class="flex flex-col gap-0.5">
                                <a href="{{ route('home') }}" class="px-3 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('home') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                    {{ __('Home') }}
                                </a>
                                @isset($menuPages)
                                    @foreach ($menuPages as $menuPage)
                                        @if ($menuPage->children->isNotEmpty())
                                            <div class="mt-1">
                                                <a href="{{ route('page.show', $menuPage->slug) }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ (request()->route('slug') ?? '') === $menuPage->slug ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                                    {{ $menuPage->getTitle($langCode ?? null) ?: $menuPage->slug }}
                                                </a>
                                                <div class="ml-3 mt-0.5 space-y-0.5 border-l-2 border-gray-200 dark:border-gray-600 pl-3">
                                                    @foreach ($menuPage->children as $child)
                                                        <a href="{{ route('page.show', $child->slug) }}" class="block py-1.5 text-sm {{ (request()->route('slug') ?? '') === $child->slug ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                                                            {{ $child->getTitle($langCode ?? null) ?: $child->slug }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <a href="{{ route('page.show', $menuPage->slug) }}" class="block px-3 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('page.show') && request()->route('slug') === $menuPage->slug ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                                {{ $menuPage->getTitle($langCode ?? null) ?: $menuPage->slug }}
                                            </a>
                                        @endif
                                    @endforeach
                                @endisset
                                @if (Route::has('login'))
                                    <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                                        @auth
                                            <a href="{{ url('/admin/dashboard') }}" class="block px-3 py-2.5 rounded-md text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                {{ __('Dashboard') }}
                                            </a>
                                        @else
                                            <a href="{{ route('login') }}" class="block px-3 py-2.5 rounded-md text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                {{ __('Log in') }}
                                            </a>
                                            @if (Route::has('register'))
                                                <a href="{{ route('register') }}" class="block px-3 py-2.5 rounded-md text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 mt-1">
                                                    {{ __('Register') }}
                                                </a>
                                            @endif
                                        @endauth
                                    </div>
                                @endif
                            </nav>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1">
                @yield('content')
            </main>
        </div>

        @stack('scripts')
        <script>
            (function() {
                var btn = document.getElementById('burger-btn');
                var panel = document.getElementById('mobile-menu');
                var iconOpen = document.getElementById('burger-icon');
                var iconClose = document.getElementById('burger-close-icon');
                if (!btn || !panel) return;
                function open() {
                    panel.classList.remove('hidden');
                    panel.setAttribute('aria-hidden', 'false');
                    btn.setAttribute('aria-expanded', 'true');
                    btn.setAttribute('aria-label', '{{ __("Close menu") }}');
                    iconOpen.classList.add('hidden');
                    iconClose.classList.remove('hidden');
                }
                function close() {
                    panel.classList.add('hidden');
                    panel.setAttribute('aria-hidden', 'true');
                    btn.setAttribute('aria-expanded', 'false');
                    btn.setAttribute('aria-label', '{{ __("Open menu") }}');
                    iconOpen.classList.remove('hidden');
                    iconClose.classList.add('hidden');
                }
                btn.addEventListener('click', function() {
                    if (panel.classList.contains('hidden')) open(); else close();
                });
            })();
        </script>
    </body>
</html>
