<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Pages') }}</h1>
        <a href="{{ route('admin.pages.create') }}" class="page-title-action">{{ __('Add Page') }}</a>
    </x-slot>

    @php
        $defaultLang = \App\Models\Language::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'en';
        $activeCount = $pages->where('is_active', true)->count();
        $inactiveCount = $pages->where('is_active', false)->count();
    @endphp

    <div class="wp-index" data-list>
        <x-notification />

        <div class="list-head">
            <ul class="subsubsub">
                <li><a href="#" data-status-link="all" class="current">{{ __('All') }} <span class="count">({{ $pages->count() }})</span></a> |</li>
                <li><a href="#" data-status-link="active">{{ __('Published') }} <span class="count">({{ $activeCount }})</span></a> |</li>
                <li><a href="#" data-status-link="inactive">{{ __('Drafts') }} <span class="count">({{ $inactiveCount }})</span></a></li>
            </ul>
            <div class="search-box">
                <input type="search" class="wp-search" placeholder="{{ __('Search pages') }}">
            </div>
        </div>

        <div class="tablenav top">
            <span class="displaying-num">{{ $pages->count() }} {{ $pages->count() === 1 ? __('item') : __('items') }}</span>
        </div>

        <div class="table-scroll">
            <table class="wp-list-table">
                <thead>
                    <tr>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Slug') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $page)
                        @php $title = $page->getTitle($langCode); @endphp
                        <tr data-status="{{ $page->is_active ? 'active' : 'inactive' }}" data-search="{{ mb_strtolower($title.' '.$page->slug, 'UTF-8') }}">
                            <td class="column-primary">
                                <strong><a href="{{ route('admin.pages.edit', $page) }}">{{ $title }}</a></strong>
                                <div class="row-actions">
                                    <span><a href="{{ route('admin.pages.edit', $page) }}">{{ __('Edit') }}</a></span>
                                    @if ($page->is_active)
                                        <span class="muted"> | </span>
                                        <span><a href="{{ route('page.show', $page->slug) }}" target="_blank" rel="noopener">{{ __('View') }}</a></span>
                                    @endif
                                    <span class="muted"> | </span>
                                    <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">{{ __('Trash') }}</button>
                                    </form>
                                </div>
                            </td>
                            <td>{{ $page->slug }}</td>
                            <td>
                                <span class="post-state">{{ $page->is_active ? __('Published') : __('Draft') }}</span>
                                {{ $page->updated_at?->format('Y/m/d \a\t g:i a') }}
                            </td>
                        </tr>
                    @endforeach
                    <tr class="no-items" @if ($pages->isNotEmpty()) hidden @endif>
                        <td colspan="3">{{ __('No pages found.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
