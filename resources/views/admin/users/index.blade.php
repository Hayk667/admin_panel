<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Users') }}</h1>
    </x-slot>

    @php
        $verifiedCount = $users->filter(fn ($user) => (bool) $user->email_verified_at)->count();
        $unverifiedCount = $users->count() - $verifiedCount;
    @endphp

    <div class="wp-index" data-list>
        <x-notification />

        <div class="list-head">
            <ul class="subsubsub">
                <li><a href="#" data-status-link="all" class="current">{{ __('All') }} <span class="count">({{ $users->count() }})</span></a> |</li>
                <li><a href="#" data-status-link="verified">{{ __('Verified') }} <span class="count">({{ $verifiedCount }})</span></a> |</li>
                <li><a href="#" data-status-link="unverified">{{ __('Unverified') }} <span class="count">({{ $unverifiedCount }})</span></a></li>
            </ul>
            <div class="search-box">
                <input type="search" class="wp-search" placeholder="{{ __('Search users') }}">
            </div>
        </div>

        <div class="tablenav top">
            <span class="displaying-num">{{ $users->count() }} {{ $users->count() === 1 ? __('item') : __('items') }}</span>
        </div>

        <div class="table-scroll">
            <table class="wp-list-table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Role') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr data-status="{{ $user->email_verified_at ? 'verified' : 'unverified' }}" data-search="{{ mb_strtolower($user->name.' '.$user->email.' '.($user->role->name ?? ''), 'UTF-8') }}">
                            <td class="column-primary">
                                <strong>
                                    @if (auth()->user()->isAdmin())
                                        <a href="{{ route('admin.users.edit', $user) }}">{{ $user->name }}</a>
                                    @else
                                        {{ $user->name }}
                                    @endif
                                </strong>
                                <div class="row-actions">
                                    @if (auth()->user()->isAdmin())
                                        <span><a href="{{ route('admin.users.edit', $user) }}">{{ __('Edit') }}</a></span>
                                    @else
                                        <span class="muted">{{ __('View only') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role ? $user->role->name : __('No role') }}</td>
                        </tr>
                    @endforeach
                    <tr class="no-items" @if ($users->isNotEmpty()) hidden @endif>
                        <td colspan="3">{{ __('No users found.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
