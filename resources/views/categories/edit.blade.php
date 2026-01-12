<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Category') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('categories.update', $category) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug <span class="text-gray-500 text-xs">(auto-generated if empty)</span></label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty to auto-generate from name</p>
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name (Multilingual)</label>
                            @if($languages->count() > 0)
                                <div class="mt-2">
                                    <!-- Tab Navigation -->
                                    <div class="border-b border-gray-200 dark:border-gray-700">
                                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                            @foreach($languages as $index => $language)
                                                <button type="button" 
                                                    class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $index === 0 ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                                                    data-tab="lang-{{ $language->code }}">
                                                    {{ $language->name }} ({{ strtoupper($language->code) }})
                                                </button>
                                            @endforeach
                                        </nav>
                                    </div>

                                    <!-- Tab Content -->
                                    @foreach($languages as $index => $language)
                                        <div class="tab-content {{ $index === 0 ? '' : 'hidden' }} mt-4" id="lang-{{ $language->code }}">
                                            <label for="name_{{ $language->code }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Name
                                            </label>
                                            <input type="text" name="name[{{ $language->code }}]" id="name_{{ $language->code }}" 
                                                value="{{ old("name.{$language->code}", $category->name[$language->code] ?? '') }}" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            @error("name.{$language->code}")
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('categories.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Cancel</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                {{ __('Update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slugInput = document.getElementById('slug');
            @php
                $defaultLang = \App\Models\Language::getDefault();
                $defaultLangCode = $defaultLang ? $defaultLang->code : 'en';
            @endphp
            const defaultLangCode = '{{ $defaultLangCode }}';
            const defaultLangNameInput = document.querySelector(`input[name="name[${defaultLangCode}]"]`);
            
            if (defaultLangNameInput && slugInput) {
                // Auto-generate slug when default language name loses focus (blur)
                defaultLangNameInput.addEventListener('blur', function() {
                    if (!slugInput.value || slugInput.value.trim() === '') {
                        generateSlug(defaultLangNameInput.value, slugInput);
                    }
                });
            }
            
            function generateSlug(text, targetInput) {
                if (!text) return;
                // Generate slug with underscores
                let slug = text.toLowerCase()
                    .trim()
                    .replace(/[^\w\s]/g, '') // Remove special characters (keep alphanumeric and spaces)
                    .replace(/\s+/g, '_') // Replace spaces with underscores
                    .replace(/_+/g, '_') // Replace multiple underscores with single underscore
                    .replace(/^_+|_+$/g, ''); // Remove leading/trailing underscores
                targetInput.value = slug;
            }

            // Tab switching functionality
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

