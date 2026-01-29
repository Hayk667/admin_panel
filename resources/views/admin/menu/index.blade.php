<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Menu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-notification />

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ __('Drag and drop to reorder menu items. Only active pages are shown.') }}
                    </p>

                    @if ($pages->isEmpty())
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('No active pages. Activate pages in') }}
                            <a href="{{ route('admin.pages.index') }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">{{ __('Pages') }}</a>
                            {{ __('to see them here.') }}
                        </div>
                    @else
                        <ul id="menu-sortable" class="space-y-2">
                            @php
                                $defaultLang = \App\Models\Language::getDefault();
                                $langCode = $defaultLang ? $defaultLang->code : 'en';
                            @endphp
                            @foreach ($pages as $page)
                                <li class="menu-item flex items-center gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 cursor-grab active:cursor-grabbing transition shadow-sm hover:shadow"
                                    data-id="{{ $page->id }}">
                                    <span class="text-gray-400 dark:text-gray-500 shrink-0" aria-hidden="true">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                        </svg>
                                    </span>
                                    <span class="flex-1 font-medium text-gray-900 dark:text-gray-100">{{ $page->getTitle($langCode) ?: $page->slug }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $page->slug }}</span>
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium shrink-0">{{ __('Edit') }}</a>
                                </li>
                            @endforeach
                        </ul>

                        <div id="menu-save-status" class="mt-4 text-sm text-gray-500 dark:text-gray-400 hidden" role="status">
                            {{ __('Saving order…') }}
                        </div>
                        <div id="menu-save-success" class="mt-4 text-sm text-green-600 dark:text-green-400 hidden" role="status">
                            {{ __('Order saved.') }}
                        </div>
                        <div id="menu-save-error" class="mt-4 text-sm text-red-600 dark:text-red-400 hidden" role="alert">
                            {{ __('Failed to save order. Please try again.') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('admin.pages.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 text-sm font-medium">
                    ← {{ __('Back to Pages') }}
                </a>
            </div>
        </div>
    </div>

    @if (!$pages->isEmpty())
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const list = document.getElementById('menu-sortable');
                const statusEl = document.getElementById('menu-save-status');
                const successEl = document.getElementById('menu-save-success');
                const errorEl = document.getElementById('menu-save-error');

                function showStatus() {
                    statusEl.classList.remove('hidden');
                    successEl.classList.add('hidden');
                    errorEl.classList.add('hidden');
                }
                function showSuccess() {
                    statusEl.classList.add('hidden');
                    successEl.classList.remove('hidden');
                    errorEl.classList.add('hidden');
                    setTimeout(function() { successEl.classList.add('hidden'); }, 3000);
                }
                function showError() {
                    statusEl.classList.add('hidden');
                    successEl.classList.add('hidden');
                    errorEl.classList.remove('hidden');
                }

                const sortable = new Sortable(list, {
                    animation: 150,
                    ghostClass: 'opacity-50',
                    chosenClass: 'ring-2 ring-indigo-500 dark:ring-indigo-400',
                    dragClass: 'shadow-lg',
                    onEnd: function() {
                        const order = Array.from(list.querySelectorAll('.menu-item')).map(function(el) {
                            return parseInt(el.getAttribute('data-id'), 10);
                        });

                        showStatus();

                        fetch('{{ route("admin.menu.reorder") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ order: order })
                        })
                        .then(function(res) { return res.ok ? res.json() : Promise.reject(res); })
                        .then(function() { showSuccess(); })
                        .catch(function() { showError(); });
                    }
                });
            });
        </script>
        @endpush
    @endif
</x-app-layout>
