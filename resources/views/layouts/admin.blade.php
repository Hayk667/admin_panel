<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <script>
            if (localStorage.getItem('adminSidebarFolded') === '1') {
                document.documentElement.classList.add('folded');
            }

            function adminShell() {
                return {
                    folded: localStorage.getItem('adminSidebarFolded') === '1',
                    mobileOpen: false,
                    helpOpen: false,
                    columnsOpen: false,
                    init() {
                        this.$watch('folded', function (value) {
                            localStorage.setItem('adminSidebarFolded', value ? '1' : '0');
                            document.documentElement.classList.toggle('folded', value);
                        });
                    }
                };
            }

            document.addEventListener('alpine:init', function () {
                var defaults = { thumb: false, views: true, likes: false, rate: false };
                var saved = {};
                try {
                    saved = JSON.parse(localStorage.getItem('postScreenCols') || '{}') || {};
                } catch (e) {
                    saved = {};
                }
                Alpine.store('postCols', {
                    cols: Object.assign({}, defaults, saved),
                    toggle: function (key) {
                        this.cols[key] = !this.cols[key];
                        localStorage.setItem('postScreenCols', JSON.stringify(this.cols));
                    }
                });
            });
        </script>
    </head>
    <body class="wp-admin antialiased" x-data="adminShell()" :class="{ 'mobile-open': mobileOpen }">
        @php
            $postsActive = request()->routeIs('admin.posts.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.tags.*');
            $pagesActive = request()->routeIs('admin.pages.*');
            $settingsActive = request()->routeIs('admin.languages.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.page-permissions.*') || request()->routeIs('admin.deleted-content.*');
        @endphp

        <div class="wp-overlay" @click="mobileOpen = false"></div>

        <aside class="wp-sidebar">
            <div class="wp-scroll" @click="if ($event.target.closest('a')) mobileOpen = false">
                <a class="wp-brand" href="{{ route('home') }}" title="{{ __('Visit site') }}">
                    <span class="wp-brand-mark">{{ strtoupper(substr(config('app.name', 'A'), 0, 1)) }}</span>
                    <span class="wp-menu-label">{{ config('app.name') }}</span>
                </a>

                <ul class="wp-menu-list">
                    <li class="wp-menu {{ request()->routeIs('admin.dashboard') ? 'current' : '' }}">
                        <a href="{{ route('admin.dashboard') }}" title="{{ __('Dashboard') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 4h7v7H4zM13 4h7v4h-7zM13 10h7v10h-7zM4 13h7v7H4z" stroke-linejoin="round"/></svg>
                            <span class="wp-menu-label">{{ __('Dashboard') }}</span>
                        </a>
                    </li>

                    <li class="wp-menu has-sub {{ $postsActive ? 'current' : '' }}">
                        <a href="{{ route('admin.posts.index') }}" title="{{ __('Posts') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M8 4h8l1 4h3v2h-1.2L16 20H8L5.2 10H4V8h3l1-4z" stroke-linejoin="round"/><path d="M9 10h6"/></svg>
                            <span class="wp-menu-label">{{ __('Posts') }}</span>
                        </a>
                        <ul class="wp-submenu">
                            <li class="wp-submenu-head">{{ __('Posts') }}</li>
                            <li><a href="{{ route('admin.posts.index') }}" class="{{ request()->routeIs('admin.posts.index') ? 'current' : '' }}">{{ __('All Posts') }}</a></li>
                            <li><a href="{{ route('admin.posts.create') }}" class="{{ request()->routeIs('admin.posts.create') ? 'current' : '' }}">{{ __('Add Post') }}</a></li>
                            <li><a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'current' : '' }}">{{ __('Categories') }}</a></li>
                            <li><a href="{{ route('admin.tags.index') }}" class="{{ request()->routeIs('admin.tags.*') ? 'current' : '' }}">{{ __('Tags') }}</a></li>
                        </ul>
                    </li>

                    <li class="wp-menu has-sub {{ $pagesActive ? 'current' : '' }}">
                        <a href="{{ route('admin.pages.index') }}" title="{{ __('Pages') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3h7l5 5v13H7V3z" stroke-linejoin="round"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                            <span class="wp-menu-label">{{ __('Pages') }}</span>
                        </a>
                        <ul class="wp-submenu">
                            <li class="wp-submenu-head">{{ __('Pages') }}</li>
                            <li><a href="{{ route('admin.pages.index') }}" class="{{ request()->routeIs('admin.pages.index') ? 'current' : '' }}">{{ __('All Pages') }}</a></li>
                            <li><a href="{{ route('admin.pages.create') }}" class="{{ request()->routeIs('admin.pages.create') ? 'current' : '' }}">{{ __('Add Page') }}</a></li>
                        </ul>
                    </li>

                    <li class="wp-menu {{ request()->routeIs('admin.menu.*') ? 'current' : '' }}">
                        <a href="{{ route('admin.menu.index') }}" title="{{ __('Menu') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                            <span class="wp-menu-label">{{ __('Menu') }}</span>
                        </a>
                    </li>

                    <li class="wp-menu-sep"></li>

                    <li class="wp-menu {{ request()->routeIs('admin.users.*') ? 'current' : '' }}">
                        <a href="{{ route('admin.users.index') }}" title="{{ __('Users') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 12a4 4 0 100-8 4 4 0 000 8z"/><path d="M4 20a8 8 0 0116 0"/></svg>
                            <span class="wp-menu-label">{{ __('Users') }}</span>
                        </a>
                    </li>

                    <li class="wp-menu {{ request()->routeIs('admin.languages.*') ? 'current' : '' }}">
                        <a href="{{ route('admin.languages.index') }}" title="{{ __('Languages') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.8 3.8 5.8 3.8 9S14.5 18.2 12 21c-2.5-2.8-3.8-5.8-3.8-9S9.5 5.8 12 3z"/></svg>
                            <span class="wp-menu-label">{{ __('Languages') }}</span>
                        </a>
                    </li>

                    <li class="wp-menu {{ request()->routeIs('admin.deleted-content.*') ? 'current' : '' }}">
                        <a href="{{ route('admin.deleted-content.index') }}" title="{{ __('Deleted content') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 7h14M9 7V5h6v2M8 7l1 13h6l1-13"/></svg>
                            <span class="wp-menu-label">{{ __('Deleted content') }}</span>
                        </a>
                    </li>

                    @if (Auth::check() && Auth::user()->isAdmin())
                        <li class="wp-menu {{ request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.page-permissions.*') ? 'current' : '' }}">
                            <a href="{{ route('admin.roles.index') }}" title="{{ __('Roles & Permissions') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z"/></svg>
                                <span class="wp-menu-label">{{ __('Roles') }}</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="wp-footer">
                @auth
                    <div class="wp-user" x-data="{ open: false }" @click.away="open = false">
                        <button type="button" class="wp-user-btn" @click="open = !open" title="{{ Auth::user()->name }}">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <img class="avatar-img" src="{{ Auth::user()->profile_photo_url }}" alt="">
                            @else
                                <span class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            @endif
                            <span class="wp-menu-label">{{ Auth::user()->name }}</span>
                        </button>
                        <div class="wp-user-menu" x-show="open" x-cloak>
                            <a href="{{ route('profile.show') }}">{{ __('Profile') }}</a>
                            <a href="{{ route('home') }}" target="_blank" rel="noopener">{{ __('Visit site') }}</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">{{ __('Log Out') }}</button>
                            </form>
                        </div>
                    </div>
                @endauth
                <button type="button" class="collapse-button" @click="folded = !folded" :title="folded ? '{{ __('Expand menu') }}' : '{{ __('Collapse menu') }}'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" :style="folded ? 'transform: rotate(180deg)' : ''"><path d="M15 6l-6 6 6 6"/></svg>
                    <span class="collapse-label" x-text="folded ? '{{ __('Expand') }}' : '{{ __('Collapse menu') }}'"></span>
                </button>
            </div>
        </aside>

        <div class="wp-content">
            <div class="wp-mobile-bar">
                <button type="button" @click="mobileOpen = !mobileOpen" aria-label="{{ __('Menu') }}">☰</button>
                <span>{{ config('app.name') }}</span>
            </div>

            <div class="screen-meta">
                @if (request()->routeIs('admin.posts.index'))
                    <div class="screen-meta-wrap" @click.away="columnsOpen = false">
                        <button type="button" class="screen-meta-btn" @click="columnsOpen = !columnsOpen; helpOpen = false" :aria-expanded="columnsOpen ? 'true' : 'false'">{{ __('Screen Options') }}</button>
                        <div class="screen-meta-panel" x-show="columnsOpen" x-cloak>
                            <fieldset>
                                <legend>{{ __('Columns') }}</legend>
                                <label><input type="checkbox" :checked="$store.postCols.cols.thumb" @change="$store.postCols.toggle('thumb')"> {{ __('Thumbnail') }}</label>
                                <label><input type="checkbox" :checked="$store.postCols.cols.views" @change="$store.postCols.toggle('views')"> {{ __('Views') }}</label>
                                <label><input type="checkbox" :checked="$store.postCols.cols.likes" @change="$store.postCols.toggle('likes')"> {{ __('Likes') }}</label>
                                <label><input type="checkbox" :checked="$store.postCols.cols.rate" @change="$store.postCols.toggle('rate')"> {{ __('Rating') }}</label>
                            </fieldset>
                        </div>
                    </div>
                @endif
                <div class="screen-meta-wrap" @click.away="helpOpen = false">
                    <button type="button" class="screen-meta-btn" @click="helpOpen = !helpOpen; columnsOpen = false" :aria-expanded="helpOpen ? 'true' : 'false'">{{ __('Help') }}</button>
                    <div class="screen-meta-panel" x-show="helpOpen" x-cloak>
                        <p>{{ __('Filter a list by status, then search or use the dropdowns to narrow it further. Check rows to run a bulk action.') }}</p>
                        <p><a href="{{ route('home') }}" target="_blank" rel="noopener">{{ __('Visit site') }}</a></p>
                    </div>
                </div>
            </div>

            @if (isset($header))
                <div class="wp-heading">
                    {{ $header }}
                </div>
            @endif

            <div class="wp-body">
                {{ $slot }}
            </div>
        </div>

        @stack('modals')
        @stack('scripts')

        @livewireScripts

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-list]').forEach(function (root) {
                    var status = 'all';
                    var term = '';
                    var rows = function () {
                        return Array.prototype.slice.call(root.querySelectorAll('tbody tr[data-status]'));
                    };
                    var apply = function () {
                        var visible = 0;
                        rows().forEach(function (row) {
                            var okStatus = status === 'all' || row.getAttribute('data-status') === status;
                            var haystack = (row.getAttribute('data-search') || '').toLowerCase();
                            var show = okStatus && (term === '' || haystack.indexOf(term) !== -1);
                            row.hidden = !show;
                            if (show) visible++;
                        });
                        var empty = root.querySelector('.no-items');
                        if (empty) empty.hidden = visible !== 0;
                        var count = root.querySelector('.displaying-num');
                        if (count) count.textContent = visible + (visible === 1 ? ' item' : ' items');
                    };
                    root.querySelectorAll('[data-status-link]').forEach(function (link) {
                        link.addEventListener('click', function (event) {
                            event.preventDefault();
                            status = link.getAttribute('data-status-link');
                            root.querySelectorAll('[data-status-link]').forEach(function (other) {
                                other.classList.toggle('current', other === link);
                            });
                            apply();
                        });
                    });
                    var input = root.querySelector('.wp-search');
                    if (input) {
                        input.addEventListener('input', function () {
                            term = input.value.trim().toLowerCase();
                            apply();
                        });
                    }
                });

                document.querySelectorAll('.posts-filter').forEach(function (form) {
                    var action = form.querySelector('input[name="action"]');
                    form.querySelectorAll('.bulk-apply').forEach(function (button) {
                        button.addEventListener('click', function (event) {
                            var select = form.querySelector(button.getAttribute('data-select'));
                            var value = select ? select.value : '';
                            if (action) action.value = value;
                            var checked = form.querySelectorAll('.post-cb:checked').length;
                            if (!value || value === '-1' || checked === 0) {
                                event.preventDefault();
                                window.alert('Select an action and at least one item.');
                            }
                        });
                    });
                    form.querySelectorAll('.cb-select-all').forEach(function (selectAll) {
                        selectAll.addEventListener('change', function () {
                            var checked = selectAll.checked;
                            form.querySelectorAll('.cb-select-all').forEach(function (box) {
                                box.checked = checked;
                            });
                            form.querySelectorAll('.post-cb').forEach(function (box) {
                                var row = box.closest('tr');
                                if (!row || !row.hidden) box.checked = checked;
                            });
                        });
                    });
                });

                document.querySelectorAll('.filter-apply').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var root = button.closest('.wp-posts');
                        if (!root) return;
                        var params = new URLSearchParams(window.location.search);
                        var month = root.querySelector('.filter-month');
                        var category = root.querySelector('.filter-category');
                        if (month && month.value) params.set('m', month.value); else params.delete('m');
                        if (category && category.value) params.set('category', category.value); else params.delete('category');
                        params.delete('page');
                        var query = params.toString();
                        window.location = window.location.pathname + (query ? '?' + query : '');
                    });
                });

                var notifications = document.querySelectorAll('.notification-alert');
                notifications.forEach(function (notification) {
                    setTimeout(function () {
                        notification.style.transition = 'opacity 0.5s ease-out';
                        notification.style.opacity = '0';
                        setTimeout(function () {
                            notification.remove();
                        }, 500);
                    }, 5000);
                });

                document.querySelectorAll('form[method="POST"]').forEach(function (form) {
                    var methodInput = form.querySelector('input[name="_method"][value="DELETE"]');
                    if (!methodInput) return;
                    form.addEventListener('submit', function (event) {
                        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                            event.preventDefault();
                        }
                    });
                });
            });
        </script>
    </body>
</html>
