<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Deleted content') }}</h1>
    </x-slot>

    @php
        $typeCounts = $items->groupBy('type');
    @endphp

    <div class="wp-index" data-list>
        <x-notification />

        <div class="list-head">
            <ul class="subsubsub">
                <li><a href="#" data-status-link="all" class="current">{{ __('All') }} <span class="count">({{ $items->count() }})</span></a>@if ($typeCounts->isNotEmpty()) |@endif</li>
                @foreach ($typeCounts as $type => $group)
                    <li><a href="#" data-status-link="{{ $type }}">{{ $group->first()->type_label }} <span class="count">({{ $group->count() }})</span></a>@if (! $loop->last) |@endif</li>
                @endforeach
            </ul>
            <div class="search-box">
                <input type="search" class="wp-search" placeholder="{{ __('Search deleted content') }}">
            </div>
        </div>

        <div class="tablenav top">
            <span class="displaying-num">{{ $items->count() }} {{ $items->count() === 1 ? __('item') : __('items') }}</span>
        </div>

        <div class="table-scroll">
            <table class="wp-list-table">
                <thead>
                    <tr>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Deleted') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr data-status="{{ $item->type }}" data-search="{{ mb_strtolower($item->title.' '.$item->type_label, 'UTF-8') }}">
                            <td class="column-primary">
                                <strong>{{ $item->title }}</strong>
                                <div class="row-actions is-visible">
                                    <form action="{{ route('admin.deleted-content.restore', ['type' => $item->type, 'id' => $item->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="row-action-link">{{ __('Restore') }}</button>
                                    </form>
                                    <span class="muted"> | </span>
                                    <form action="{{ route('admin.deleted-content.force-delete', ['type' => $item->type, 'id' => $item->id]) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">{{ __('Delete permanently') }}</button>
                                    </form>
                                </div>
                            </td>
                            <td>{{ $item->type_label }}</td>
                            <td>{{ $item->deleted_at?->format('Y/m/d \a\t g:i a') }}</td>
                        </tr>
                    @endforeach
                    <tr class="no-items" @if ($items->isNotEmpty()) hidden @endif>
                        <td colspan="3">{{ __('No deleted content.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
