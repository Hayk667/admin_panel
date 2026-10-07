<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Categories') }}</h1>
        <a href="{{ route('admin.categories.create') }}" class="page-title-action">{{ __('Add Category') }}</a>
    </x-slot>

    @php
        $defaultLang = \App\Models\Language::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'en';
        $activeCount = $categories->where('is_active', true)->count();
        $inactiveCount = $categories->where('is_active', false)->count();
    @endphp

    <div class="wp-index" data-list>
        <x-notification />

        <div class="list-head">
            <ul class="subsubsub">
                <li><a href="#" data-status-link="all" class="current">{{ __('All') }} <span class="count">({{ $categories->count() }})</span></a> |</li>
                <li><a href="#" data-status-link="active">{{ __('Active') }} <span class="count">({{ $activeCount }})</span></a> |</li>
                <li><a href="#" data-status-link="inactive">{{ __('Inactive') }} <span class="count">({{ $inactiveCount }})</span></a></li>
            </ul>
            <div class="search-box">
                <input type="search" class="wp-search" placeholder="{{ __('Search categories') }}">
            </div>
        </div>

        <div class="tablenav top">
            <span class="displaying-num">{{ $categories->count() }} {{ $categories->count() === 1 ? __('item') : __('items') }}</span>
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
                    @foreach ($categories as $category)
                        @php $name = $category->getName($langCode); @endphp
                        <tr data-status="{{ $category->is_active ? 'active' : 'inactive' }}" data-search="{{ mb_strtolower($name.' '.$category->slug, 'UTF-8') }}">
                            <td class="column-primary">
                                <strong><a href="{{ route('admin.categories.edit', $category) }}">{{ $name }}</a></strong>
                                @unless ($category->is_active)
                                    — <span class="muted">{{ __('Inactive') }}</span>
                                @endunless
                                <div class="row-actions">
                                    <span><a href="{{ route('admin.categories.edit', $category) }}">{{ __('Edit') }}</a></span>
                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('delete_category'))
                                        <span class="muted"> | </span>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">{{ __('Delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $category->slug }}</td>
                            <td>
                                <a href="{{ route('admin.posts.index', ['category' => $category->id]) }}">{{ $category->posts_count ?? 0 }}</a>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="no-items" @if ($categories->isNotEmpty()) hidden @endif>
                        <td colspan="3">{{ __('No categories found.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
