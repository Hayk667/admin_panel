<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Posts') }}</h1>
        <a href="{{ route('admin.posts.create') }}" class="page-title-action">{{ __('Add Post') }}</a>
    </x-slot>

    @php
        $activeLanguages = $languages->where('is_active', true);
        $inactiveLanguages = $languages->where('is_active', false);
        $dateSort = $order === 'asc'
            ? collect($filters)->except('order')->all()
            : array_merge($filters, ['order' => 'asc']);
        $hasExtraFilters = collect($filters)->except('status')->isNotEmpty();
    @endphp

    <div class="wp-posts">
        <x-notification />

        <div class="list-head">
            <div>
                <ul class="subsubsub">
                    <li>
                        <a href="{{ route('admin.posts.index', collect($filters)->except('status')->all()) }}" class="{{ $status === 'all' ? 'current' : '' }}">{{ __('All') }} <span class="count">({{ $counts['all'] }})</span></a> |
                    </li>
                    <li>
                        <a href="{{ route('admin.posts.index', array_merge($filters, ['status' => 'mine'])) }}" class="{{ $status === 'mine' ? 'current' : '' }}">{{ __('Mine') }} <span class="count">({{ $counts['mine'] }})</span></a> |
                    </li>
                    <li>
                        <a href="{{ route('admin.posts.index', array_merge($filters, ['status' => 'published'])) }}" class="{{ $status === 'published' ? 'current' : '' }}">{{ __('Published') }} <span class="count">({{ $counts['published'] }})</span></a> |
                    </li>
                    <li>
                        <a href="{{ route('admin.posts.index', array_merge($filters, ['status' => 'drafts'])) }}" class="{{ $status === 'drafts' ? 'current' : '' }}">{{ __('Drafts') }} <span class="count">({{ $counts['drafts'] }})</span></a>
                    </li>
                </ul>

                <p class="lang-line">
                    @foreach ($activeLanguages as $language)
                        <a href="{{ route('admin.posts.index', array_merge($filters, ['lang' => $language->code])) }}" class="{{ $languageFilter && $languageFilter->code === $language->code ? 'current' : '' }}">{{ $language->name }} <span class="count">({{ $languageCounts[$language->code] ?? 0 }})</span></a>
                        @if (! $loop->last) | @endif
                    @endforeach
                    @if ($activeLanguages->isNotEmpty()) | @endif
                    <a href="{{ route('admin.posts.index', collect($filters)->except('lang')->all()) }}" class="{{ $languageFilter ? '' : 'current' }}">{{ __('All languages') }} <span class="count">({{ $counts['all'] }})</span></a>
                </p>

                @if ($inactiveLanguages->isNotEmpty())
                    <p class="lang-line">
                        <span class="label">{{ __('Inactive:') }}</span>
                        @foreach ($inactiveLanguages as $language)
                            <a href="{{ route('admin.posts.index', array_merge($filters, ['lang' => $language->code])) }}" class="{{ $languageFilter && $languageFilter->code === $language->code ? 'current' : '' }}">{{ $language->name }} <span class="count">({{ $languageCounts[$language->code] ?? 0 }})</span></a>
                            @if (! $loop->last) | @endif
                        @endforeach
                    </p>
                @endif
            </div>

            <form class="search-box" method="GET" action="{{ route('admin.posts.index') }}">
                @foreach (collect($filters)->except('s') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label class="sr-only" for="post-search">{{ __('Search Posts') }}</label>
                <input id="post-search" type="search" name="s" value="{{ $search }}" placeholder="{{ __('Search posts') }}">
                <button type="submit" class="button">{{ __('Search Posts') }}</button>
            </form>
        </div>

        <form method="POST" action="{{ route('admin.posts.bulk') }}" class="posts-filter">
            @csrf
            <input type="hidden" name="action" value="">

            <div class="tablenav top">
                <div class="actions">
                    <label class="sr-only" for="bulk-action">{{ __('Bulk actions') }}</label>
                    <select id="bulk-action" class="bulk-top">
                        <option value="-1">{{ __('Bulk actions') }}</option>
                        <option value="publish">{{ __('Publish') }}</option>
                        <option value="draft">{{ __('Move to draft') }}</option>
                        <option value="trash">{{ __('Move to Trash') }}</option>
                    </select>
                    <button type="submit" class="button bulk-apply" data-select=".bulk-top">{{ __('Apply') }}</button>
                </div>
                <div class="actions">
                    <label class="sr-only" for="filter-month">{{ __('All dates') }}</label>
                    <select id="filter-month" class="filter-month">
                        <option value="">{{ __('All dates') }}</option>
                        @foreach ($months as $row)
                            <option value="{{ $row->ym }}" @selected((string) $month === (string) $row->ym)>{{ $row->label }} ({{ $row->total }})</option>
                        @endforeach
                    </select>
                    <label class="sr-only" for="filter-category">{{ __('All Categories') }}</label>
                    <select id="filter-category" class="filter-category">
                        <option value="">{{ __('All Categories') }}</option>
                        <option value="none" @selected($categoryId === 'none')>{{ __('Uncategorized') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->getName($langCode) }} ({{ $category->posts_count }})</option>
                        @endforeach
                    </select>
                    <button type="button" class="button filter-apply">{{ __('Filter') }}</button>
                    @if ($hasExtraFilters)
                        <a class="button" href="{{ route('admin.posts.index', $status !== 'all' ? ['status' => $status] : []) }}">{{ __('Clear') }}</a>
                    @endif
                </div>
                @include('admin.partials.posts-pagination')
            </div>

            <div class="table-scroll">
                <table class="wp-list-table">
                    <thead>
                        <tr>
                            <td class="check-column"><input type="checkbox" class="cb-select-all" aria-label="{{ __('Select all') }}"></td>
                            <th class="column-title">{{ __('Title') }}</th>
                            <th class="col-optional" :class="$store.postCols.cols.thumb ? 'col-show' : ''">{{ __('Thumbnail') }}</th>
                            <th>{{ __('Languages') }}</th>
                            <th class="column-author">{{ __('Author') }}</th>
                            <th>{{ __('Categories') }}</th>
                            <th>{{ __('Tags') }}</th>
                            <th class="column-views" :class="$store.postCols.cols.views ? 'col-show' : 'col-optional'">
                                <span title="{{ __('Views') }}">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                </span>
                            </th>
                            <th class="column-likes col-optional" :class="$store.postCols.cols.likes ? 'col-show' : ''">{{ __('Likes') }}</th>
                            <th class="column-rate col-optional" :class="$store.postCols.cols.rate ? 'col-show' : ''">{{ __('Rating') }}</th>
                            <th class="column-date">
                                <a href="{{ route('admin.posts.index', $dateSort) }}">{{ __('Date') }} {{ $order === 'asc' ? '↑' : '↓' }}</a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            @php
                                $displayCode = $languageFilter ? $languageFilter->code : $langCode;
                                $postTitle = $post->getTitle($displayCode);
                                if ($postTitle === '') {
                                    $postTitle = $post->getTitle($langCode);
                                }
                                $canEdit = auth()->user()->canEditPost($post);
                                $canDelete = auth()->user()->canDeletePost($post);
                            @endphp
                            <tr>
                                <th class="check-column" scope="row">
                                    <input type="checkbox" class="post-cb" name="ids[]" value="{{ $post->id }}" aria-label="{{ __('Select') }} {{ $postTitle !== '' ? $postTitle : __('(no title)') }}">
                                </th>
                                <td class="column-title">
                                    <div class="title-row">
                                        <strong>
                                            <a href="{{ $canEdit ? route('admin.posts.edit', $post) : route('admin.posts.show', $post) }}">
                                                {{ $postTitle !== '' ? $postTitle : __('(no title)') }}
                                            </a>
                                        </strong>
                                        <span class="title-icons">
                                            @if ($canEdit)
                                                <a href="{{ route('admin.posts.edit', $post) }}" title="{{ __('Edit') }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 20h4l10-10-4-4L4 16v4z"/><path d="M13 7l4 4"/></svg>
                                                </a>
                                            @endif
                                            @if ($post->is_active)
                                                <a href="{{ route('post.show', $post->slug) }}" target="_blank" rel="noopener" title="{{ __('View') }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M14 5h5v5"/><path d="M10 14L19 5"/><path d="M19 13v6a1 1 0 01-1 1H6a1 1 0 01-1-1V6a1 1 0 011-1h6"/></svg>
                                                </a>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="row-actions">
                                        @if ($canEdit)
                                            <span><a href="{{ route('admin.posts.edit', $post) }}">{{ __('Edit') }}</a></span>
                                        @else
                                            <span><a href="{{ route('admin.posts.show', $post) }}">{{ __('View') }}</a></span>
                                        @endif
                                        @if ($post->is_active)
                                            <span class="muted"> | </span>
                                            <span><a href="{{ route('post.show', $post->slug) }}" target="_blank" rel="noopener">{{ __('View') }}</a></span>
                                        @endif
                                        @if ($canDelete)
                                            <span class="muted"> | </span>
                                            <span>
                                                <button type="submit" form="delete-post-{{ $post->id }}">{{ __('Trash') }}</button>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="col-optional" :class="$store.postCols.cols.thumb ? 'col-show' : ''">
                                    @if ($post->thumbnail)
                                        <img class="thumb" src="{{ asset('storage/' . $post->thumbnail) }}" alt="">
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="lang-pills">
                                        @foreach ($languages as $language)
                                            @php $value = is_array($post->title) ? ($post->title[$language->code] ?? null) : null; @endphp
                                            @if (is_string($value) && trim($value) !== '')
                                                <span class="lang-pill" title="{{ $language->name }}">{{ strtoupper($language->code) }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @if ($post->createdUser)
                                        <a href="{{ route('admin.posts.index', array_merge($filters, ['author' => $post->created_user_id])) }}">{{ $post->createdUser->name }}</a>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($post->category)
                                        <a href="{{ route('admin.posts.index', array_merge($filters, ['category' => $post->category_id])) }}">{{ $post->category->getName($langCode) }}</a>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @forelse ($post->tags as $tag)
                                        <a href="{{ route('admin.posts.index', array_merge($filters, ['tag' => $tag->id])) }}">{{ $tag->getName($langCode) }}</a>@if (! $loop->last), @endif
                                    @empty
                                        <span class="muted">—</span>
                                    @endforelse
                                </td>
                                <td class="column-views" :class="$store.postCols.cols.views ? 'col-show' : 'col-optional'">{{ $post->view_count ?? 0 }}</td>
                                <td class="column-likes col-optional" :class="$store.postCols.cols.likes ? 'col-show' : ''">{{ $post->likes ?? 0 }}</td>
                                <td class="column-rate col-optional" :class="$store.postCols.cols.rate ? 'col-show' : ''">{{ number_format($post->rate ?? 0, 1) }}</td>
                                <td class="column-date">
                                    <span class="post-state">{{ $post->is_active ? __('Published') : __('Draft') }}</span>
                                    {{ $post->created_at?->format('Y/m/d \a\t g:i a') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11">{{ __('No posts found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="tablenav bottom">
                <div class="actions">
                    <label class="sr-only" for="bulk-action-bottom">{{ __('Bulk actions') }}</label>
                    <select id="bulk-action-bottom" class="bulk-bottom">
                        <option value="-1">{{ __('Bulk actions') }}</option>
                        <option value="publish">{{ __('Publish') }}</option>
                        <option value="draft">{{ __('Move to draft') }}</option>
                        <option value="trash">{{ __('Move to Trash') }}</option>
                    </select>
                    <button type="submit" class="button bulk-apply" data-select=".bulk-bottom">{{ __('Apply') }}</button>
                </div>
                @include('admin.partials.posts-pagination')
            </div>
        </form>

        @foreach ($posts as $post)
            @if (auth()->user()->canDeletePost($post))
                <form id="delete-post-{{ $post->id }}" action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="delete-form">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        @endforeach
    </div>
</x-app-layout>
