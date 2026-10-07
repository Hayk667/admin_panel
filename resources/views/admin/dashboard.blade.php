<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Dashboard') }}</h1>
    </x-slot>

    @php
        $defaultLang = \App\Models\Language::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'en';
    @endphp

    <div class="wp-dashboard">
        <div class="welcome-panel" x-data="{ show: localStorage.getItem('hideWelcomePanel') !== '1' }" x-show="show" x-cloak>
            <button type="button" class="welcome-dismiss" @click="show = false; localStorage.setItem('hideWelcomePanel', '1')" aria-label="{{ __('Dismiss') }}">×</button>
            <div class="welcome-columns">
                <div>
                    <h2>{{ __('Welcome') }}</h2>
                    <p>{{ __('This is your site dashboard. From here you can write posts, edit pages, and see what was published recently.') }}</p>
                </div>
                <div>
                    <h2>{{ __('Next steps') }}</h2>
                    <ul>
                        <li><a href="{{ route('admin.posts.create') }}">{{ __('Write a new post') }}</a></li>
                        <li><a href="{{ route('admin.pages.create') }}">{{ __('Add a new page') }}</a></li>
                        <li><a href="{{ route('admin.menu.index') }}">{{ __('Arrange the menu') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h2>{{ __('More actions') }}</h2>
                    <ul>
                        <li><a href="{{ route('admin.posts.index') }}">{{ __('Manage posts') }}</a></li>
                        <li><a href="{{ route('admin.categories.index') }}">{{ __('Manage categories') }}</a></li>
                        <li><a href="{{ route('home') }}" target="_blank" rel="noopener">{{ __('Visit site') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dashboard-widgets">
            <section class="postbox">
                <h2>{{ __('At a Glance') }}</h2>
                <div class="inside">
                    <ul class="glance-list">
                        <li><a href="{{ route('admin.posts.index') }}">{{ $postsTotal }} {{ __('Posts') }}</a></li>
                        <li><a href="{{ route('admin.pages.index') }}">{{ $pagesTotal }} {{ __('Pages') }}</a></li>
                        <li><a href="{{ route('admin.categories.index') }}">{{ $categoriesTotal }} {{ __('Categories') }}</a></li>
                        <li><a href="{{ route('admin.tags.index') }}">{{ $tagsTotal }} {{ __('Tags') }}</a></li>
                        <li><a href="{{ route('admin.languages.index') }}">{{ $languagesTotal }} {{ __('Languages') }}</a></li>
                        <li><a href="{{ route('admin.users.index') }}">{{ $usersTotal }} {{ __('Users') }}</a></li>
                    </ul>
                    <p class="glance-meta">
                        <a href="{{ route('admin.posts.index', ['status' => 'published']) }}">{{ $postsActive }} {{ __('Published') }}</a>
                        ·
                        <a href="{{ route('admin.posts.index', ['status' => 'drafts']) }}">{{ $postsInactive }} {{ __('Drafts') }}</a>
                    </p>
                </div>
            </section>

            <section class="postbox">
                <h2>{{ __('Activity') }}</h2>
                <div class="inside">
                    @if ($recentPosts->isEmpty())
                        <p class="glance-meta">{{ __('No posts yet.') }}</p>
                    @else
                        <ul class="activity-list">
                            @foreach ($recentPosts as $post)
                                @php $title = $post->getTitle($langCode); @endphp
                                <li>
                                    <a href="{{ auth()->user()->canEditPost($post) ? route('admin.posts.edit', $post) : route('admin.posts.show', $post) }}">{{ $title !== '' ? $title : __('(no title)') }}</a>
                                    <span class="meta">
                                        {{ $post->is_active ? __('Published') : __('Draft') }}
                                        @if ($post->createdUser)
                                            · {{ $post->createdUser->name }}
                                        @endif
                                        · {{ $post->created_at?->diffForHumans() }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <section class="postbox wide">
                <h2>{{ __('Content') }}</h2>
                <div class="inside">
                    <div class="chart-grid">
                        <div>
                            <h3>{{ __('Posts') }}</h3>
                            <canvas id="postsChart"></canvas>
                        </div>
                        <div>
                            <h3>{{ __('Categories') }}</h3>
                            <canvas id="categoriesChart"></canvas>
                        </div>
                        <div>
                            <h3>{{ __('Languages') }}</h3>
                            <canvas id="languagesChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var textColor = '#50575e';
            var options = {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: textColor }
                    }
                }
            };

            function doughnut(id, active, inactive) {
                var canvas = document.getElementById(id);
                if (!canvas) return;
                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Inactive'],
                        datasets: [{
                            data: [active, inactive],
                            backgroundColor: ['#00a32a', '#d63638'],
                            borderWidth: 0
                        }]
                    },
                    options: options
                });
            }

            doughnut('postsChart', {{ (int) $postsActive }}, {{ (int) $postsInactive }});
            doughnut('categoriesChart', {{ (int) $categoriesActive }}, {{ (int) $categoriesInactive }});
            doughnut('languagesChart', {{ (int) $languagesActive }}, {{ (int) $languagesInactive }});
        });
    </script>
    @endpush
</x-app-layout>
