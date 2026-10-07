<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Tags') }}</h1>
        <a href="{{ route('admin.tags.create') }}" class="page-title-action">{{ __('Add Tag') }}</a>
    </x-slot>

    @php
        $defaultLang = \App\Models\Language::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'en';
        $activeCount = $tags->where('is_active', true)->count();
        $inactiveCount = $tags->where('is_active', false)->count();
    @endphp

    <div class="wp-index" data-list>
        <x-notification />

        <div class="list-head">
            <ul class="subsubsub">
                <li><a href="#" data-status-link="all" class="current">{{ __('All') }} <span class="count">({{ $tags->count() }})</span></a> |</li>
                <li><a href="#" data-status-link="active">{{ __('Active') }} <span class="count">({{ $activeCount }})</span></a> |</li>
                <li><a href="#" data-status-link="inactive">{{ __('Inactive') }} <span class="count">({{ $inactiveCount }})</span></a></li>
            </ul>
            <div class="search-box">
                <input type="search" class="wp-search" placeholder="{{ __('Search tags') }}">
            </div>
        </div>

        <div class="tablenav top">
            <span class="displaying-num">{{ $tags->count() }} {{ $tags->count() === 1 ? __('item') : __('items') }}</span>
        </div>

        <div class="table-scroll">
            <table class="wp-list-table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Slug') }}</th>
                        <th>{{ __('Count') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tags as $tag)
                        @php $name = $tag->getName($langCode); @endphp
                        <tr data-status="{{ $tag->is_active ? 'active' : 'inactive' }}" data-search="{{ mb_strtolower($name.' '.$tag->slug, 'UTF-8') }}">
                            <td class="column-primary">
                                <strong><a href="{{ route('admin.tags.edit', $tag) }}">{{ $name }}</a></strong>
                                @unless ($tag->is_active)
                                    — <span class="muted">{{ __('Inactive') }}</span>
                                @endunless
                                <div class="row-actions">
                                    <span><a href="{{ route('admin.tags.edit', $tag) }}">{{ __('Edit') }}</a></span>
                                    <span class="muted"> | </span>
                                    <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                            <td>{{ $tag->slug }}</td>
                            <td>
                                <a href="{{ route('admin.posts.index', ['tag' => $tag->id]) }}">{{ $tag->posts_count ?? 0 }}</a>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="no-items" @if ($tags->isNotEmpty()) hidden @endif>
                        <td colspan="3">{{ __('No tags found.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
