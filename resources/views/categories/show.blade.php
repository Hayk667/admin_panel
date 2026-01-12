<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Category Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <strong>Slug:</strong> {{ $category->slug }}
                    </div>
                    <div class="mb-4">
                        <strong>Name:</strong>
                        @php
                            $languages = \App\Models\Language::where('is_active', true)->get();
                            $firstLang = $languages->first();
                        @endphp
                        @if($languages->count() > 0)
                            <div class="mt-2">
                                <!-- Tab Navigation -->
                                <div class="border-b border-gray-200 dark:border-gray-700">
                                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                        @foreach($languages as $index => $lang)
                                            <button type="button" 
                                                class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $index === 0 ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                                                data-tab="lang-{{ $lang->code }}">
                                                {{ $lang->name }} ({{ strtoupper($lang->code) }})
                                            </button>
                                        @endforeach
                                    </nav>
                                </div>

                                <!-- Tab Content -->
                                @foreach($languages as $index => $lang)
                                    <div class="tab-content {{ $index === 0 ? '' : 'hidden' }} mt-4 p-4 border rounded" id="lang-{{ $lang->code }}">
                                        <p><strong>Name:</strong> {{ $category->getName($lang->code) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="mb-4">
                        <strong>Posts Count:</strong> {{ $category->posts->count() }}
                    </div>
                    <div class="mb-4">
                        <strong>Status:</strong> {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('categories.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Back to list</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');

                    // Remove active state from all buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                        btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                    });

                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });

                    // Show selected tab content
                    const targetContent = document.getElementById(targetTab);
                    if (targetContent) {
                        targetContent.classList.remove('hidden');
                    }

                    // Add active state to clicked button
                    this.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                    this.classList.add('border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                });
            });
        });
    </script>
    @endpush
</x-app-layout>

