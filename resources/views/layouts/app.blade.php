<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased" x-data="{ 
        darkMode: (() => {
            const stored = localStorage.getItem('darkMode');
            if (stored !== null) return stored === 'true';
            return window.matchMedia('(prefers-color-scheme: dark)').matches;
        })()
    }" 
    x-init="
        $watch('darkMode', val => { 
            localStorage.setItem('darkMode', val); 
            document.documentElement.classList.toggle('dark', val);
        });
        document.documentElement.classList.toggle('dark', darkMode);
    ">
        <x-banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')
        @stack('scripts')

        @livewireScripts
        
        <script>
            // Auto-hide notifications after 5 seconds
            document.addEventListener('DOMContentLoaded', function() {
                const notifications = document.querySelectorAll('.notification-alert');
                notifications.forEach(function(notification) {
                    setTimeout(function() {
                        notification.style.transition = 'opacity 0.5s ease-out';
                        notification.style.opacity = '0';
                        setTimeout(function() {
                            notification.remove();
                        }, 500);
                    }, 5000);
                });
                
                // Add confirm dialog to all delete forms
                const deleteForms = document.querySelectorAll('form[method="POST"]');
                deleteForms.forEach(function(form) {
                    const methodInput = form.querySelector('input[name="_method"][value="DELETE"]');
                    if (methodInput) {
                        form.addEventListener('submit', function(event) {
                            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                                event.preventDefault();
                                return false;
                            }
                        });
                    }
                });
            });
        </script>
    </body>
</html>
