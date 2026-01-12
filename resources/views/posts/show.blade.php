<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Post Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    @if ($post->image)
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . $post->image) }}" alt="Post image" class="max-w-md rounded">
                        </div>
                    @endif
                    <div class="mb-4">
                        <strong>Slug:</strong> {{ $post->slug }}
                    </div>
                    <div class="mb-4">
                        <strong>Category:</strong> {{ $post->category ? $post->category->getName() : 'No category' }}
                    </div>
                    <div class="mb-4">
                        <strong>Published Date:</strong> {{ $post->published_at ? $post->published_at->format('Y-m-d') : 'Not set' }}
                    </div>
                    <div class="mb-4">
                        <strong>Status:</strong> {{ $post->is_active ? 'Active' : 'Inactive' }}
                    </div>
                    <div class="mb-4">
                        <strong>Likes:</strong> {{ $post->likes ?? 0 }}
                    </div>
                    <div class="mb-4">
                        <strong>Rate:</strong> {{ number_format($post->rate ?? 0, 2) }}
                    </div>
                    <div class="mb-4">
                        <strong>Views:</strong> {{ $post->view_count ?? 0 }}
                    </div>
                    <div class="mb-4">
                        <strong>Created by:</strong> {{ $post->createdUser ? $post->createdUser->name : 'Unknown' }} ({{ $post->created_at->format('Y-m-d H:i:s') }})
                    </div>
                    @if ($post->updatedUser)
                        <div class="mb-4">
                            <strong>Last updated by:</strong> {{ $post->updatedUser->name }} ({{ $post->updated_at->format('Y-m-d H:i:s') }})
                        </div>
                    @endif
                    <div class="mb-4">
                        <strong>Title & Content:</strong>
                        @php
                            $languages = \App\Models\Language::where('is_active', true)->get();
                            $firstLang = $languages->first();
                        @endphp
                        @if($languages->count() > 0)
                            <div class="mt-4">
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
                                        <p class="mt-2"><strong>Title:</strong> {{ $post->getTitle($lang->code) }}</p>
                                        <div class="mt-2"><strong>Content:</strong> 
                                            <div class="mt-2">{!! $post->getContent($lang->code) !!}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('posts.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Back to list</a>
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

