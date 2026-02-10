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
                        {{ __('Drag and drop to reorder menu items and submenus. Only active pages are shown.') }}
                    </p>

                    @if ($pages->isEmpty())
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('No active pages. Activate pages in') }}
                            <a href="{{ route('admin.pages.index') }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">{{ __('Pages') }}</a>
                            {{ __('to see them here.') }}
                        </div>
                    @else
                        @php
                            $defaultLang = \App\Models\Language::getDefault();
                            $langCode = $defaultLang ? $defaultLang->code : 'en';
                        @endphp
                        <ul id="menu-sortable" class="space-y-2">
                            @foreach ($pages as $page)
                                <li class="menu-item group bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 transition shadow-sm hover:shadow"
                                    data-id="{{ $page->id }}">
                                    <div class="flex items-center gap-3 px-4 py-3 cursor-grab active:cursor-grabbing">
                                        <span class="text-gray-400 dark:text-gray-500 shrink-0" aria-hidden="true">
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                            </svg>
                                        </span>
                                        <span class="flex-1 font-medium text-gray-900 dark:text-gray-100">{{ $page->getTitle($langCode) ?: $page->slug }}</span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $page->slug }}</span>
                                        <a href="{{ route('admin.pages.edit', $page) }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium shrink-0" onclick="event.stopPropagation();">{{ __('Edit') }}</a>
                                    </div>
                                    @if ($page->children->isNotEmpty())
                                        <ul class="menu-sub-sortable ml-6 mt-1 mb-2 space-y-1 border-l-2 border-gray-200 dark:border-gray-600 pl-4">
                                            @foreach ($page->children as $child)
                                                <li class="menu-item flex items-center gap-3 px-3 py-2 bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 cursor-grab active:cursor-grabbing text-sm"
                                                    data-id="{{ $child->id }}">
                                                    <span class="text-gray-400 dark:text-gray-500 shrink-0">
                                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                                        </svg>
                                                    </span>
                                                    <span class="flex-1 font-medium text-gray-800 dark:text-gray-200">{{ $child->getTitle($langCode) ?: $child->slug }}</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $child->slug }}</span>
                                                    <a href="{{ route('admin.pages.edit', $child) }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium shrink-0" onclick="event.stopPropagation();">{{ __('Edit') }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <ul class="menu-sub-sortable empty-sub ml-6 mt-1 mb-2 space-y-1 border-l-2 border-dashed border-gray-300 dark:border-gray-600 pl-4 min-h-12 rounded-r bg-gray-50/50 dark:bg-gray-700/30" data-placeholder="{{ __('Drop here for submenu') }}"></ul>
                                    @endif
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
        <style>
            .sortable-chosen { box-shadow: 0 0 0 2px rgb(99 102 241); }
            .dark .sortable-chosen { box-shadow: 0 0 0 2px rgb(129 140 248); }
            .menu-sub-sortable.empty-sub:empty::before,
            .menu-sub-sortable.empty-sub:not(:has(li.menu-item))::before {
                content: attr(data-placeholder);
                display: block;
                font-size: 0.75rem;
                color: #9ca3af;
                font-style: italic;
            }
            .dark .menu-sub-sortable.empty-sub:empty::before,
            .dark .menu-sub-sortable.empty-sub:not(:has(li.menu-item))::before { color: #6b7280; }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            (function() {
                function runSortable() {
                    if (typeof window.Sortable === 'undefined') {
                        setTimeout(runSortable, 50);
                        return;
                    }
                    var list = document.getElementById('menu-sortable');
                    if (!list) return;
                    var statusEl = document.getElementById('menu-save-status');
                    var successEl = document.getElementById('menu-save-success');
                    var errorEl = document.getElementById('menu-save-error');

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

                    function buildNestedOrder() {
                        var order = [];
                        var mainList = document.getElementById('menu-sortable');
                        var topItems = mainList.querySelectorAll(':scope > li.menu-item');
                        for (var i = 0; i < topItems.length; i++) {
                            var li = topItems[i];
                            var id = parseInt(li.getAttribute('data-id'), 10);
                            var subUl = li.querySelector(':scope > ul.menu-sub-sortable');
                            var children = [];
                            if (subUl) {
                                var subItems = subUl.querySelectorAll(':scope > li.menu-item');
                                for (var j = 0; j < subItems.length; j++) {
                                    children.push({ id: parseInt(subItems[j].getAttribute('data-id'), 10) });
                                }
                            }
                            order.push({ id: id, children: children });
                        }
                        return order;
                    }

                    function saveOrder() {
                        showStatus();
                        var order = buildNestedOrder();
                        var token = document.querySelector('meta[name="csrf-token"]');
                        var url = '{{ route("admin.menu.reorder") }}';
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
                            },
                            body: JSON.stringify({ order: order })
                        })
                        .then(function(res) { return res.ok ? res.json() : Promise.reject(res); })
                        .then(function() { showSuccess(); })
                        .catch(function() { showError(); });
                    }

                    var groupCfg = { name: 'menu', pull: true, put: true };

                    function onDragEnd() {
                        setTimeout(saveOrder, 0);
                    }

                    window.Sortable.create(list, {
                        animation: 150,
                        ghostClass: 'opacity-50',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'shadow-lg',
                        group: groupCfg,
                        draggable: 'li.menu-item',
                        emptyInsertThreshold: 20,
                        onEnd: onDragEnd
                    });

                    var subLists = list.querySelectorAll('ul.menu-sub-sortable');
                    for (var k = 0; k < subLists.length; k++) {
                        window.Sortable.create(subLists[k], {
                            animation: 150,
                            ghostClass: 'opacity-50',
                            chosenClass: 'sortable-chosen',
                            dragClass: 'shadow-lg',
                            group: groupCfg,
                            draggable: 'li.menu-item',
                            emptyInsertThreshold: 30,
                            onEnd: onDragEnd
                        });
                    }
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', runSortable);
                } else {
                    runSortable();
                }
            })();
        </script>
        @endpush
    @endif
</x-app-layout>
