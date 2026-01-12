<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Post') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data" id="postForm" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug <span class="text-gray-500 text-xs">(auto-generated if empty)</span></label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $post->slug) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty to auto-generate from title</p>
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title & Content (Multilingual)</label>
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
                                            <div class="mb-4">
                                                <label for="title_{{ $language->code }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                    Title
                                                </label>
                                                <input type="text" name="title[{{ $language->code }}]" id="title_{{ $language->code }}" 
                                                    value="{{ old("title.{$language->code}", $post->title[$language->code] ?? '') }}" required
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                @error("title.{$language->code}")
                                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div class="mb-4">
                                                <label for="content_{{ $language->code }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                    Content
                                                </label>
                                                <textarea name="content[{{ $language->code }}]" id="content_{{ $language->code }}" rows="10" data-required="true"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white editor">{{ old("content.{$language->code}", $post->content[$language->code] ?? '') }}</textarea>
                                                @error("content.{$language->code}")
                                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <select name="category_id" id="category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Select a category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->getName() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="published_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Published Date</label>
                            <input type="date" name="published_at" id="published_at" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d') : '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('published_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Image</label>
                            @if ($post->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $post->image) }}" alt="Current image" class="h-32 w-32 object-cover rounded mb-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Current image</p>
                                </div>
                            @endif
                            <input type="file" name="image" id="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty to keep current image</p>
                            @error('image')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $post->is_active) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('posts.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Cancel</a>
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
    <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key', 'no-api-key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '.editor',
                height: 400,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic forecolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'image | removeformat | help',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                images_upload_handler: function (blobInfo, progress) {
                    return new Promise((resolve, reject) => {
                        const formData = new FormData();
                        formData.append('image', blobInfo.blob(), blobInfo.filename());
                        formData.append('_token', '{{ csrf_token() }}');

                        fetch('{{ route("upload.image") }}', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('HTTP error! status: ' + response.status);
                            }
                            return response.json();
                        })
                        .then(result => {
                            if (result.location) {
                                resolve(result.location);
                            } else {
                                reject('Upload failed: ' + (result.error || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            reject('Upload failed: ' + error.message);
                        });
                    });
                },
                automatic_uploads: true,
                file_picker_types: 'image',
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });
                }
            });
            
            // Form validation before submit
            const form = document.getElementById('postForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Update all TinyMCE editors before validation
                    if (typeof tinymce !== 'undefined') {
                        tinymce.triggerSave();
                    }
                    
                    // Validate required content fields
                    const requiredContentFields = document.querySelectorAll('textarea[data-required="true"]');
                    let isValid = true;
                    
                    requiredContentFields.forEach(function(field) {
                        const editor = tinymce.get(field.id);
                        const content = editor ? editor.getContent() : field.value;
                        
                        // Remove HTML tags and check if content is empty
                        const textContent = content.replace(/<[^>]*>/g, '').trim();
                        
                        if (!textContent) {
                            isValid = false;
                            field.classList.add('border-red-500');
                            const errorMsg = field.parentElement.querySelector('.field-error');
                            if (!errorMsg) {
                                const errorDiv = document.createElement('p');
                                errorDiv.className = 'mt-1 text-sm text-red-600 dark:text-red-400 field-error';
                                errorDiv.textContent = 'This field is required.';
                                field.parentElement.appendChild(errorDiv);
                            }
                        } else {
                            field.classList.remove('border-red-500');
                            const errorMsg = field.parentElement.querySelector('.field-error');
                            if (errorMsg) {
                                errorMsg.remove();
                            }
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required content fields.');
                        return false;
                    }
                });
            }
            
            // Track form submission and cleanup unused images
            let formSubmitted = false;
            
            form.addEventListener('submit', function(e) {
                formSubmitted = true;
            });
            
            // Cleanup temp images when leaving without saving
            window.addEventListener('beforeunload', function(e) {
                if (!formSubmitted) {
                    // Use sendBeacon for reliable cleanup on page unload
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    navigator.sendBeacon('{{ route("upload.cleanup") }}', formData);
                }
            });
            
            // Clear tracking when form is successfully submitted
            if (form) {
                const originalAction = form.action;
                form.addEventListener('submit', function(e) {
                    if (formSubmitted) {
                        // Clear tracking (don't delete images, they're saved)
                        fetch('{{ route("upload.clear-tracking") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).catch(() => {}); // Ignore errors
                    }
                });
            }
            
            // Cleanup when clicking Cancel link
            const cancelLink = document.querySelector('a[href="{{ route("posts.index") }}"]');
            if (cancelLink) {
                cancelLink.addEventListener('click', function(e) {
                    if (!formSubmitted) {
                        // Cleanup temp images
                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        navigator.sendBeacon('{{ route("upload.cleanup") }}', formData);
                    }
                });
            }
            
            // Auto-generate slug from title
            const slugInput = document.getElementById('slug');
            @php
                $defaultLang = \App\Models\Language::getDefault();
                $defaultLangCode = $defaultLang ? $defaultLang->code : 'en';
            @endphp
            const defaultLangCode = '{{ $defaultLangCode }}';
            const defaultLangTitleInput = document.querySelector(`input[name="title[${defaultLangCode}]"]`);
            
            if (defaultLangTitleInput && slugInput) {
                // Auto-generate slug when default language title loses focus (blur)
                defaultLangTitleInput.addEventListener('blur', function() {
                    if (!slugInput.value || slugInput.value.trim() === '') {
                        generateSlug(defaultLangTitleInput.value, slugInput);
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
                        // Reinitialize TinyMCE for the visible editor if needed
                        if (typeof tinymce !== 'undefined') {
                            const editorId = targetContent.querySelector('.editor')?.id;
                            if (editorId && !tinymce.get(editorId)) {
                                tinymce.init({
                                    selector: '#' + editorId,
                                    height: 400,
                                    menubar: false,
                                    plugins: [
                                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                                        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                                        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                                    ],
                                    toolbar: 'undo redo | blocks | ' +
                                        'bold italic forecolor | alignleft aligncenter ' +
                                        'alignright alignjustify | bullist numlist outdent indent | ' +
                                        'image | removeformat | help',
                                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                                    images_upload_handler: function (blobInfo, progress) {
                                        return new Promise((resolve, reject) => {
                                            const formData = new FormData();
                                            formData.append('image', blobInfo.blob(), blobInfo.filename());
                                            formData.append('_token', '{{ csrf_token() }}');

                                            fetch('{{ route("upload.image") }}', {
                                                method: 'POST',
                                                body: formData
                                            })
                                            .then(response => {
                                                if (!response.ok) {
                                                    throw new Error('HTTP error! status: ' + response.status);
                                                }
                                                return response.json();
                                            })
                                            .then(result => {
                                                if (result.location) {
                                                    resolve(result.location);
                                                } else {
                                                    reject('Upload failed: ' + (result.error || 'Unknown error'));
                                                }
                                            })
                                            .catch(error => {
                                                reject('Upload failed: ' + error.message);
                                            });
                                        });
                                    },
                                    automatic_uploads: true,
                                    file_picker_types: 'image',
                                    setup: function(editor) {
                                        editor.on('change', function() {
                                            editor.save();
                                        });
                                    }
                                });
                            }
                        }
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

