<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Languages') }}</h1>
        <a href="{{ route('admin.languages.create') }}" class="page-title-action">{{ __('Add Language') }}</a>
    </x-slot>

    @php
        $activeCount = $languages->where('is_active', true)->count();
        $inactiveCount = $languages->where('is_active', false)->count();
    @endphp

    <div class="wp-index" data-list>
        <x-notification />

        <div class="list-head">
            <ul class="subsubsub">
                <li><a href="#" data-status-link="all" class="current">{{ __('All') }} <span class="count">({{ $languages->count() }})</span></a> |</li>
                <li><a href="#" data-status-link="active">{{ __('Active') }} <span class="count">({{ $activeCount }})</span></a> |</li>
                <li><a href="#" data-status-link="inactive">{{ __('Inactive') }} <span class="count">({{ $inactiveCount }})</span></a></li>
            </ul>
            <div class="search-box">
                <input type="search" class="wp-search" placeholder="{{ __('Search languages') }}">
            </div>
        </div>

        <div class="tablenav top">
            <span class="displaying-num">{{ $languages->count() }} {{ $languages->count() === 1 ? __('item') : __('items') }}</span>
        </div>

        <div class="table-scroll">
            <table class="wp-list-table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Default') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($languages as $language)
                        <tr data-status="{{ $language->is_active ? 'active' : 'inactive' }}" data-search="{{ mb_strtolower($language->name.' '.$language->code, 'UTF-8') }}">
                            <td class="column-primary">
                                <strong><a href="{{ route('admin.languages.edit', $language) }}">{{ $language->name }}</a></strong>
                                @unless ($language->is_active)
                                    — <span class="muted">{{ __('Inactive') }}</span>
                                @endunless
                                <div class="row-actions">
                                    <span><a href="{{ route('admin.languages.edit', $language) }}">{{ __('Edit') }}</a></span>
                                    @if ((auth()->user()->isAdmin() || auth()->user()->hasPermission('delete_language')) && ! $language->is_default)
                                        <span class="muted"> | </span>
                                        <form action="{{ route('admin.languages.destroy', $language) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">{{ __('Delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            <td><span class="lang-pill">{{ strtoupper($language->code) }}</span></td>
                            <td>{{ $language->is_default ? __('Default') : '—' }}</td>
                        </tr>
                    @endforeach
                    <tr class="no-items" @if ($languages->isNotEmpty()) hidden @endif>
                        <td colspan="3">{{ __('No languages found.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
