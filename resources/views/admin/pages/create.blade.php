<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create New Page') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <x-notification />
                    <x-validation-errors class="mb-4" />
                    <form method="POST" action="{{ route('admin.pages.store') }}" id="page-form">
                        @csrf

                        {{-- Page layout builder (Title, Description, Inputs are multilingual; slug is generated from first Title block) --}}
                        <div class="mb-6 border-t border-gray-200 dark:border-gray-600 pt-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Page layout') }}</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Add blocks and drag to reorder. Each block type can be used multiple times.') }}</p>

                            <div class="flex flex-wrap gap-2 mb-4">
                                <select id="add-block-type" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                    <option value="">{{ __('Add block…') }}</option>
                                    <option value="banner">{{ __('Banner') }}</option>
                                    <option value="slider">{{ __('Slider') }}</option>
                                    <option value="title">{{ __('Title') }}</option>
                                    <option value="description">{{ __('Description') }}</option>
                                    <option value="inputs">{{ __('Inputs (Text, Email, Phone)') }}</option>
                                    <option value="send_email_form">{{ __('Send Email Form') }}</option>
                                </select>
                                <button type="button" id="add-block-btn" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    {{ __('Add') }}
                                </button>
                            </div>

                            <input type="hidden" name="sections" id="sections-input" value="">

                            <ul id="sections-list" class="space-y-2 min-h-[60px] rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-2">
                                {{-- Blocks rendered by JS --}}
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Active') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.pages.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">{{ __('Cancel') }}</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                {{ __('Create') }}
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
            const languages = @json($languages->map(fn($l) => ['code' => $l->code, 'name' => $l->name]));

            let sections = @json(old('sections', []));
            if (!Array.isArray(sections)) sections = [];
            if (sections.length === 0) {
                sections = [{ id: uid(), type: 'title', data: emptyData('title') }];
            }
            sections = sections.map(s => ({
                id: (s.id && String(s.id).startsWith('s-')) ? s.id : uid(),
                type: s.type || 'title',
                data: s.data || emptyData(s.type || 'title')
            }));

            const sectionLabels = {
                banner: '{{ __("Banner") }}',
                slider: '{{ __("Slider") }}',
                title: '{{ __("Title") }}',
                description: '{{ __("Description") }}',
                inputs: '{{ __("Inputs") }}',
                send_email_form: '{{ __("Send Email Form") }}'
            };

            const phoneTypes = [
                { value: 'mobile', label: '{{ __("Mobile") }}' },
                { value: 'home', label: '{{ __("Home") }}' },
                { value: 'work', label: '{{ __("Work") }}' }
            ];

            function uid() {
                return 's-' + Date.now() + '-' + Math.random().toString(36).slice(2, 11);
            }

            function emptyData(type) {
                const data = {};
                const langObj = {};
                languages.forEach(l => { langObj[l.code] = ''; });
                switch (type) {
                    case 'banner':
                        data.image = '';
                        data.title = { ...langObj };
                        data.subtitle = { ...langObj };
                        data.full_width = false;
                        break;
                    case 'slider':
                        data.slides = [{ image: '', title: { ...langObj }, subtitle: { ...langObj } }];
                        data.full_width = false;
                        break;
                    case 'title':
                        data.title = { ...langObj };
                        break;
                    case 'description':
                        data.content = { ...langObj };
                        break;
                    case 'inputs':
                        data.inputs = [{ input_type: 'text', phone_type: 'mobile', label: { ...langObj }, placeholder: { ...langObj } }];
                        break;
                    case 'send_email_form':
                        data.form_title = { ...langObj };
                        data.email_label = { ...langObj };
                        data.content = { ...langObj };
                        data.content_label = { ...langObj };
                        data.send_button = { ...langObj };
                        break;
                    default:
                        data.raw = {};
                }
                return data;
            }

            function ensureLangKeys(obj) {
                const out = {};
                languages.forEach(l => { out[l.code] = obj[l.code] != null ? obj[l.code] : ''; });
                return out;
            }

            function syncSectionsInput() {
                if (typeof tinymce !== 'undefined') {
                    sections.forEach(section => {
                        if (section.type === 'description' && section.data.content) {
                            languages.forEach(l => {
                                const editorId = 'page-section-' + section.id + '-desc-' + l.code;
                                const ed = tinymce.get(editorId);
                                if (ed) section.data.content[l.code] = ed.getContent();
                            });
                        } else if (section.type === 'send_email_form' && section.data.content) {
                            languages.forEach(l => {
                                const editorId = 'page-section-' + section.id + '-form-content-' + l.code;
                                const ed = tinymce.get(editorId);
                                if (ed) section.data.content[l.code] = ed.getContent();
                            });
                        }
                    });
                }
                document.getElementById('sections-input').value = JSON.stringify(sections);
            }

            function removeSection(id) {
                sections = sections.filter(s => s.id !== id);
                renderSections();
                syncSectionsInput();
            }

            function addSection(type, expandNew) {
                sections.push({
                    id: uid(),
                    type: type,
                    data: emptyData(type)
                });
                renderSections();
                syncSectionsInput();
                if (expandNew) expandLastSection();
            }

            function expandLastSection() {
                const items = document.querySelectorAll('.section-item');
                if (items.length) {
                    const last = items[items.length - 1];
                    const editor = last.querySelector('.section-editor');
                    const toggleBtn = last.querySelector('.section-toggle');
                    if (editor) editor.classList.remove('hidden');
                    if (toggleBtn) {
                        toggleBtn.textContent = 'Collapse';
                        toggleBtn.setAttribute('aria-expanded', 'true');
                    }
                    if (typeof tinymce !== 'undefined' && editor) {
                        editor.querySelectorAll('.editor').forEach(ta => {
                            if (ta.id && !tinymce.get(ta.id)) initSectionEditor(ta);
                        });
                    }
                }
            }

            function initSectionEditor(ta) {
                const sectionId = ta.closest('.section-item').dataset.sectionId;
                const section = sections.find(s => s.id === sectionId);
                const lang = ta.dataset.lang;
                const key = ta.dataset.key;
                tinymce.init({
                    selector: '#' + ta.id,
                    height: 400,
                    menubar: false,
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                    ],
                    toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | image | removeformat | code | help',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                    extended_valid_elements: '+iframe[src|width|height|frameborder|allowfullscreen|allow|title|class|style]',
                    valid_children: '+body[iframe]',
                    images_upload_handler: function (blobInfo, progress) {
                        return new Promise((resolve, reject) => {
                            const formData = new FormData();
                            formData.append('image', blobInfo.blob(), blobInfo.filename());
                            formData.append('_token', '{{ csrf_token() }}');
                            fetch('{{ route("admin.upload.image") }}', { method: 'POST', body: formData })
                                .then(r => r.ok ? r.json() : Promise.reject(new Error('Upload failed')))
                                .then(result => result.location ? resolve(result.location) : reject(result.error || 'Unknown'))
                                .catch(e => reject(e.message || 'Upload failed'));
                        });
                    },
                    automatic_uploads: true,
                    file_picker_types: 'image',
                    setup: function(ed) {
                        ed.on('change', function() {
                            if (section && section.data[key]) {
                                section.data[key][lang] = ed.getContent();
                                syncSectionsInput();
                            }
                        });
                        ed.on('init', function() { ed.save(); });
                    }
                });
            }

            function updateSectionData(id, data) {
                const s = sections.find(s => s.id === id);
                if (s) s.data = data;
                syncSectionsInput();
            }

            function moveSection(fromIndex, toIndex) {
                if (toIndex < 0 || toIndex >= sections.length) return;
                const [item] = sections.splice(fromIndex, 1);
                sections.splice(toIndex, 0, item);
                renderSections();
                syncSectionsInput();
            }

            function getBlockEditorHTML(section) {
                const type = section.type;
                const data = section.data || emptyData(type);
                const id = section.id;
                const prefix = `section-${id}`;

                let inner = '';
                if (type === 'banner') {
                    const hasImage = data.image && (String(data.image).startsWith('http') || String(data.image).startsWith('/'));
                    inner = `
                        <div class="banner-image-preview mb-3 ${!hasImage ? 'hidden' : ''}">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Preview</label>
                            <img src="${hasImage ? String(data.image).replace(/"/g, '&quot;') : ''}" alt="" class="banner-preview-img max-h-24 rounded border border-gray-300 dark:border-gray-600" ${!hasImage ? 'style="display:none"' : ''}>
                        </div>
                        <div class="mb-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Upload image</label>
                            <input type="file" class="banner-image-upload w-full text-sm text-gray-500 dark:text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Or image URL</label>
                            <input type="text" class="block-data-input w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-key="image" value="${(data.image || '')}" placeholder="https://...">
                        </div>
                        <label class="flex items-center gap-2 mb-3">
                            <input type="checkbox" class="block-data-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" data-key="full_width" ${data.full_width ? 'checked' : ''}>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Full width') }}</span>
                        </label>
                        ${languages.map(l => `
                            <div class="mb-2">
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Title (${l.code})</label>
                                <input type="text" class="block-data-lang w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-lang="${l.code}" data-key="title" value="${(data.title && data.title[l.code]) || ''}">
                            </div>
                            <div class="mb-2">
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Subtitle (${l.code})</label>
                                <input type="text" class="block-data-lang w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-lang="${l.code}" data-key="subtitle" value="${(data.subtitle && data.subtitle[l.code]) || ''}">
                            </div>
                        `).join('')}
                    `;
                } else if (type === 'slider') {
                    const slides = data.slides || [{ image: '', title: {}, subtitle: {} }];
                    inner = `
                        <label class="flex items-center gap-2 mb-3">
                            <input type="checkbox" class="block-data-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" data-key="full_width" ${data.full_width ? 'checked' : ''}>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Full width') }}</span>
                        </label>
                        <div class="slider-slides-list space-y-3" data-section-id="${id}">
                            ${slides.map((slide, slideIdx) => {
                                const hasImage = slide.image && (String(slide.image).startsWith('http') || String(slide.image).startsWith('/'));
                                return `
                                    <div class="slider-slide border border-gray-200 dark:border-gray-600 rounded p-3" data-slide-index="${slideIdx}">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Slide ${slideIdx + 1}</span>
                                            <button type="button" class="remove-slider-slide text-red-600 hover:text-red-800 text-xs">Remove</button>
                                        </div>
                                        <div class="slider-image-preview mb-2 ${!hasImage ? 'hidden' : ''}">
                                            <img src="${hasImage ? String(slide.image).replace(/"/g, '&quot;') : ''}" alt="" class="slider-preview-img max-h-20 rounded border border-gray-300 dark:border-gray-600" ${!hasImage ? 'style="display:none"' : ''}>
                                        </div>
                                        <div class="mb-2">
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Upload image</label>
                                            <input type="file" class="slider-image-upload w-full text-sm text-gray-500 dark:text-gray-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" data-slide-index="${slideIdx}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Or image URL</label>
                                            <input type="text" class="slider-slide-image-input block w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-slide-index="${slideIdx}" value="${(slide.image || '')}" placeholder="https://... or /storage/...">
                                        </div>
                                        ${languages.map(l => `
                                            <div class="mb-2">
                                                <label class="block text-xs text-gray-500 dark:text-gray-400">Title (${l.code})</label>
                                                <input type="text" class="slider-slide-lang w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-lang="${l.code}" data-slide-index="${slideIdx}" data-field="title" value="${(slide.title && slide.title[l.code]) || ''}">
                                            </div>
                                            <div class="mb-2">
                                                <label class="block text-xs text-gray-500 dark:text-gray-400">Subtitle (${l.code})</label>
                                                <input type="text" class="slider-slide-lang w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-lang="${l.code}" data-slide-index="${slideIdx}" data-field="subtitle" value="${(slide.subtitle && slide.subtitle[l.code]) || ''}">
                                            </div>
                                        `).join('')}
                                    </div>
                                `;
                            }).join('')}
                        </div>
                        <button type="button" class="add-slider-slide mt-2 text-sm text-indigo-600 dark:text-indigo-400 hover:underline" data-section-id="${id}">+ Add slide</button>
                    `;
                } else if (type === 'title') {
                    inner = languages.map(l => `
                        <div class="mb-2">
                            <label class="block text-xs text-gray-500 dark:text-gray-400">Title (${l.code})</label>
                            <input type="text" class="block-data-lang w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-lang="${l.code}" data-key="title" value="${(data.title && data.title[l.code]) || ''}">
                        </div>
                    `).join('');
                } else if (type === 'description') {
                    inner = languages.map(l => `
                        <div class="mb-2">
                            <label class="block text-xs text-gray-500 dark:text-gray-400">Content (${l.code})</label>
                            <textarea id="page-section-${id}-desc-${l.code}" rows="10" class="block-data-lang editor mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" data-lang="${l.code}" data-key="content">${(data.content && data.content[l.code]) || ''}</textarea>
                        </div>
                    `).join('');
                } else if (type === 'inputs') {
                    const inputs = data.inputs || [{ input_type: 'text', phone_type: 'mobile', label: {}, placeholder: {} }];
                    inner = `
                        <div class="inputs-list space-y-3" data-section-id="${id}">
                            ${inputs.map((inp, idx) => {
                                const label = inp.label || {};
                                const placeholder = inp.placeholder || {};
                                const pt = inp.phone_type || 'mobile';
                                return `
                                    <div class="single-input border border-gray-200 dark:border-gray-600 rounded p-2" data-input-index="${idx}">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Input ${idx + 1}</span>
                                            <button type="button" class="remove-input text-red-600 hover:text-red-800 text-xs">Remove</button>
                                        </div>
                                        <div class="grid grid-cols-1 gap-2">
                                            <div>
                                                <label class="block text-xs text-gray-500 dark:text-gray-400">Type</label>
                                                <select class="input-type-select w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-input-index="${idx}">
                                                    <option value="text" ${(inp.input_type || 'text') === 'text' ? 'selected' : ''}>Text</option>
                                                    <option value="email" ${(inp.input_type) === 'email' ? 'selected' : ''}>Email</option>
                                                    <option value="phone" ${(inp.input_type) === 'phone' ? 'selected' : ''}>Phone</option>
                                                </select>
                                            </div>
                                            <div class="phone-type-wrap" style="display:${(inp.input_type) === 'phone' ? 'block' : 'none'}">
                                                <label class="block text-xs text-gray-500 dark:text-gray-400">Phone type</label>
                                                <select class="phone-type-select w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-input-index="${idx}">
                                                    ${phoneTypes.map(ptop => `<option value="${ptop.value}" ${pt === ptop.value ? 'selected' : ''}>${ptop.label}</option>`).join('')}
                                                </select>
                                            </div>
                                            ${languages.map(l => `
                                                <div>
                                                    <label class="block text-xs text-gray-500 dark:text-gray-400">Label (${l.code})</label>
                                                    <input type="text" class="input-field-lang w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-lang="${l.code}" data-input-index="${idx}" data-field="label" value="${(label[l.code]) || ''}">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 dark:text-gray-400">Placeholder (${l.code})</label>
                                                    <input type="text" class="input-field-lang w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-lang="${l.code}" data-input-index="${idx}" data-field="placeholder" value="${(placeholder[l.code]) || ''}">
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                        <button type="button" class="add-input mt-2 text-sm text-indigo-600 dark:text-indigo-400 hover:underline" data-section-id="${id}">+ Add input</button>
                    `;
                } else if (type === 'send_email_form') {
                    inner = `
                        ${languages.map(l => `
                            <div class="mb-2">
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Form title (${l.code})</label>
                                <input type="text" class="block-data-lang w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-lang="${l.code}" data-key="form_title" value="${(data.form_title && data.form_title[l.code]) || ''}">
                            </div>
                            <div class="mb-2">
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Email label (${l.code})</label>
                                <input type="text" class="block-data-lang w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-lang="${l.code}" data-key="email_label" value="${(data.email_label && data.email_label[l.code]) || ''}">
                            </div>
                            <div class="mb-2">
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Content (${l.code})</label>
                                <textarea id="page-section-${id}-form-content-${l.code}" rows="10" class="block-data-lang editor mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" data-lang="${l.code}" data-key="content">${(data.content && data.content[l.code]) || ''}</textarea>
                            </div>
                            <div class="mb-2">
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Content label (${l.code})</label>
                                <input type="text" class="block-data-lang w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-lang="${l.code}" data-key="content_label" value="${(data.content_label && data.content_label[l.code]) || ''}">
                            </div>
                            <div class="mb-2">
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Send button (${l.code})</label>
                                <input type="text" class="block-data-lang w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" data-lang="${l.code}" data-key="send_button" value="${(data.send_button && data.send_button[l.code]) || ''}">
                            </div>
                        `).join('')}
                    `;
                }

                return `
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700/50 overflow-hidden">
                        <div class="block-editor-body p-3 text-sm">${inner}</div>
                    </div>
                `;
            }

            function renderSections() {
                if (typeof tinymce !== 'undefined') {
                    document.querySelectorAll('#sections-list .editor').forEach(el => {
                        if (el.id && tinymce.get(el.id)) tinymce.get(el.id).remove();
                    });
                }
                const list = document.getElementById('sections-list');
                list.innerHTML = '';
                list.querySelectorAll('.section-item').forEach(el => el.remove());

                sections.forEach((section, index) => {
                    const li = document.createElement('li');
                    li.className = 'section-item flex items-stretch gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 p-0 overflow-hidden';
                    li.dataset.sectionId = section.id;
                    li.dataset.index = index;
                    li.draggable = true;
                    li.innerHTML = `
                        <div class="drag-handle flex items-center justify-center w-10 shrink-0 bg-gray-100 dark:bg-gray-600 cursor-grab active:cursor-grabbing text-gray-500 dark:text-gray-400" title="Drag to reorder">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a1 1 0 011 1v1h3a1 1 0 110 2H8v3a1 1 0 11-2 0V6H5a1 1 0 010-2h3V3a1 1 0 011-1zm-2 8a1 1 0 011 1v6a1 1 0 11-2 0v-6a1 1 0 011-1zm6 0a1 1 0 011 1v6a1 1 0 11-2 0v-6a1 1 0 011-1z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0 py-2 pr-2">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-800 dark:text-gray-200">${sectionLabels[section.type] || section.type}</span>
                                <div class="flex items-center gap-1">
                                    <button type="button" class="section-toggle px-2 py-1 text-xs rounded text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600" aria-expanded="false">Edit</button>
                                    <button type="button" class="section-remove px-2 py-1 text-xs rounded text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Remove</button>
                                </div>
                            </div>
                            <div class="section-editor hidden mt-2">${getBlockEditorHTML(section)}</div>
                        </div>
                    `;
                    list.appendChild(li);
                });

                bindSectionEvents();
                bindDragDrop();
            }

            function bindSectionEvents() {
                document.querySelectorAll('.section-toggle').forEach(btn => {
                    btn.onclick = function() {
                        const item = this.closest('.section-item');
                        const editor = item.querySelector('.section-editor');
                        const isHidden = editor.classList.toggle('hidden');
                        this.setAttribute('aria-expanded', !isHidden);
                        this.textContent = isHidden ? 'Edit' : 'Collapse';
                        if (!isHidden && typeof tinymce !== 'undefined') {
                            editor.querySelectorAll('.editor').forEach(ta => {
                                if (ta.id && !tinymce.get(ta.id)) {
                                    initSectionEditor(ta);
                                }
                            });
                        }
                    };
                });
                document.querySelectorAll('.section-remove').forEach(btn => {
                    btn.onclick = function() {
                        const id = this.closest('.section-item').dataset.sectionId;
                        removeSection(id);
                    };
                });

                document.querySelectorAll('.block-data-checkbox').forEach(input => {
                    input.addEventListener('change', function() {
                        const sectionId = this.closest('.section-item').dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section) return;
                        const key = this.dataset.key;
                        section.data[key] = this.checked;
                        syncSectionsInput();
                    });
                });
                document.querySelectorAll('.block-data-input').forEach(input => {
                    input.addEventListener('change', function() {
                        const sectionId = this.closest('.section-item').dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section) return;
                        const key = this.dataset.key;
                        section.data[key] = this.value;
                        syncSectionsInput();
                        if (key === 'image' && section.type === 'banner') {
                            const preview = this.closest('.section-item').querySelector('.banner-image-preview');
                            const img = preview && preview.querySelector('.banner-preview-img');
                            if (this.value && (this.value.startsWith('http') || this.value.startsWith('/'))) {
                                if (preview) preview.classList.remove('hidden');
                                if (img) { img.src = this.value; img.style.display = ''; }
                            } else {
                                if (preview) preview.classList.add('hidden');
                                if (img) img.style.display = 'none';
                            }
                        }
                    });
                });
                document.querySelectorAll('.slider-slide-image-input').forEach(input => {
                    input.addEventListener('change', function() {
                        const sectionId = this.closest('.section-item').dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section || section.type !== 'slider') return;
                        const slideIdx = parseInt(this.dataset.slideIndex, 10);
                        if (!section.data.slides) section.data.slides = [];
                        if (!section.data.slides[slideIdx]) return;
                        section.data.slides[slideIdx].image = this.value;
                        const preview = this.closest('.slider-slide').querySelector('.slider-image-preview');
                        const img = preview && preview.querySelector('.slider-preview-img');
                        if (this.value && (this.value.startsWith('http') || this.value.startsWith('/'))) {
                            if (preview) preview.classList.remove('hidden');
                            if (img) { img.src = this.value; img.style.display = ''; }
                        } else {
                            if (preview) preview.classList.add('hidden');
                            if (img) img.style.display = 'none';
                        }
                        syncSectionsInput();
                    });
                });
                document.querySelectorAll('.slider-slide-lang').forEach(input => {
                    input.addEventListener('change', function() {
                        const sectionId = this.closest('.section-item').dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section || section.type !== 'slider') return;
                        const slideIdx = parseInt(this.dataset.slideIndex, 10);
                        const field = this.dataset.field;
                        const lang = this.dataset.lang;
                        if (!section.data.slides || !section.data.slides[slideIdx]) return;
                        if (!section.data.slides[slideIdx][field]) section.data.slides[slideIdx][field] = {};
                        section.data.slides[slideIdx][field][lang] = this.value;
                        syncSectionsInput();
                    });
                });
                document.querySelectorAll('.slider-image-upload').forEach(input => {
                    input.addEventListener('change', function() {
                        const file = this.files[0];
                        if (!file) return;
                        const sectionItem = this.closest('.section-item');
                        const sectionId = sectionItem.dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section || section.type !== 'slider') return;
                        const slideIdx = parseInt(this.dataset.slideIndex, 10);
                        if (!section.data.slides || !section.data.slides[slideIdx]) return;
                        const formData = new FormData();
                        formData.append('image', file);
                        formData.append('_token', document.querySelector('input[name="_token"]').value);
                        const urlInput = sectionItem.querySelector('.slider-slide-image-input[data-slide-index="' + slideIdx + '"]');
                        const preview = sectionItem.querySelector('.slider-slide[data-slide-index="' + slideIdx + '"] .slider-image-preview');
                        const img = preview && preview.querySelector('.slider-preview-img');
                        fetch('{{ route("admin.upload.image") }}', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.json())
                            .then(data => {
                                if (data.location) {
                                    section.data.slides[slideIdx].image = data.location;
                                    if (urlInput) urlInput.value = data.location;
                                    if (preview) preview.classList.remove('hidden');
                                    if (img) { img.src = data.location; img.style.display = ''; }
                                    syncSectionsInput();
                                }
                            })
                            .catch(() => {});
                        this.value = '';
                    });
                });
                document.querySelectorAll('.add-slider-slide').forEach(btn => {
                    btn.onclick = function() {
                        const sectionId = this.dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section || section.type !== 'slider') return;
                        const langObj = {};
                        languages.forEach(l => { langObj[l.code] = ''; });
                        if (!section.data.slides) section.data.slides = [];
                        section.data.slides.push({ image: '', title: { ...langObj }, subtitle: { ...langObj } });
                        renderSections();
                        syncSectionsInput();
                    };
                });
                document.querySelectorAll('.remove-slider-slide').forEach(btn => {
                    btn.onclick = function() {
                        const sectionItem = this.closest('.section-item');
                        const sectionId = sectionItem.dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section || section.type !== 'slider') return;
                        const slide = this.closest('.slider-slide');
                        const slideIdx = parseInt(slide.dataset.slideIndex, 10);
                        section.data.slides.splice(slideIdx, 1);
                        if (section.data.slides.length === 0) {
                            const langObj = {};
                            languages.forEach(l => { langObj[l.code] = ''; });
                            section.data.slides.push({ image: '', title: { ...langObj }, subtitle: { ...langObj } });
                        }
                        renderSections();
                        syncSectionsInput();
                    };
                });
                document.querySelectorAll('.banner-image-upload').forEach(input => {
                    input.addEventListener('change', function() {
                        const file = this.files[0];
                        if (!file) return;
                        const sectionItem = this.closest('.section-item');
                        const sectionId = sectionItem.dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section || section.type !== 'banner') return;
                        const formData = new FormData();
                        formData.append('image', file);
                        formData.append('_token', document.querySelector('input[name="_token"]').value);
                        const urlInput = sectionItem.querySelector('.block-data-input[data-key="image"]');
                        const preview = sectionItem.querySelector('.banner-image-preview');
                        const img = preview && preview.querySelector('.banner-preview-img');
                        fetch('{{ route("admin.upload.image") }}', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.json())
                            .then(data => {
                                if (data.location) {
                                    section.data.image = data.location;
                                    if (urlInput) urlInput.value = data.location;
                                    if (preview) preview.classList.remove('hidden');
                                    if (img) { img.src = data.location; img.style.display = ''; }
                                    syncSectionsInput();
                                }
                            })
                            .catch(() => {});
                        this.value = '';
                    });
                });
                document.querySelectorAll('.block-data-lang').forEach(input => {
                    input.addEventListener('change', function() {
                        if (this.classList.contains('editor') && typeof tinymce !== 'undefined' && tinymce.get(this.id)) return;
                        const sectionId = this.closest('.section-item').dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section) return;
                        const key = this.dataset.key;
                        const lang = this.dataset.lang;
                        if (!section.data[key]) section.data[key] = {};
                        section.data[key][lang] = this.value;
                        syncSectionsInput();
                    });
                });

                document.querySelectorAll('.input-type-select').forEach(select => {
                    select.onchange = function() {
                        const sectionItem = this.closest('.section-item');
                        const sectionId = sectionItem.dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section || section.type !== 'inputs') return;
                        const idx = parseInt(this.dataset.inputIndex, 10);
                        if (!section.data.inputs[idx]) return;
                        section.data.inputs[idx].input_type = this.value;
                        const wrap = sectionItem.querySelector(`.single-input[data-input-index="${idx}"] .phone-type-wrap`);
                        if (wrap) wrap.style.display = this.value === 'phone' ? 'block' : 'none';
                        syncSectionsInput();
                    };
                });
                document.querySelectorAll('.phone-type-select').forEach(select => {
                    select.onchange = function() {
                        const sectionItem = this.closest('.section-item');
                        const sectionId = sectionItem.dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section || section.type !== 'inputs') return;
                        const idx = parseInt(this.dataset.inputIndex, 10);
                        if (!section.data.inputs[idx]) return;
                        section.data.inputs[idx].phone_type = this.value;
                        syncSectionsInput();
                    };
                });
                document.querySelectorAll('.input-field-lang').forEach(input => {
                    input.addEventListener('change', function() {
                        const sectionItem = this.closest('.section-item');
                        const sectionId = sectionItem.dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section || section.type !== 'inputs') return;
                        const idx = parseInt(this.dataset.inputIndex, 10);
                        const field = this.dataset.field;
                        const lang = this.dataset.lang;
                        if (!section.data.inputs[idx]) return;
                        if (!section.data.inputs[idx][field]) section.data.inputs[idx][field] = {};
                        section.data.inputs[idx][field][lang] = this.value;
                        syncSectionsInput();
                    });
                });

                document.querySelectorAll('.add-input').forEach(btn => {
                    btn.onclick = function() {
                        const sectionId = this.dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section || section.type !== 'inputs') return;
                        const langObj = {};
                        languages.forEach(l => { langObj[l.code] = ''; });
                        section.data.inputs.push({ input_type: 'text', phone_type: 'mobile', label: { ...langObj }, placeholder: { ...langObj } });
                        renderSections();
                        syncSectionsInput();
                    };
                });
                document.querySelectorAll('.remove-input').forEach(btn => {
                    btn.onclick = function() {
                        const sectionItem = this.closest('.section-item');
                        const sectionId = sectionItem.dataset.sectionId;
                        const section = sections.find(s => s.id === sectionId);
                        if (!section || section.type !== 'inputs') return;
                        const single = this.closest('.single-input');
                        const idx = parseInt(single.dataset.inputIndex, 10);
                        section.data.inputs.splice(idx, 1);
                        if (section.data.inputs.length === 0) section.data.inputs.push({ input_type: 'text', phone_type: 'mobile', label: {}, placeholder: {} });
                        renderSections();
                        syncSectionsInput();
                    };
                });
            }

            let draggedIndex = null;
            function bindDragDrop() {
                const list = document.getElementById('sections-list');
                list.querySelectorAll('.section-item').forEach(item => {
                    item.ondragstart = function(e) {
                        draggedIndex = parseInt(this.dataset.index, 10);
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', this.dataset.sectionId);
                        this.classList.add('opacity-50');
                    };
                    item.ondragend = function() {
                        this.classList.remove('opacity-50');
                        draggedIndex = null;
                    };
                    item.ondragover = function(e) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        this.classList.add('ring-2', 'ring-indigo-500');
                    };
                    item.ondragleave = function() {
                        this.classList.remove('ring-2', 'ring-indigo-500');
                    };
                    item.ondrop = function(e) {
                        e.preventDefault();
                        this.classList.remove('ring-2', 'ring-indigo-500');
                        const toIndex = parseInt(this.dataset.index, 10);
                        if (draggedIndex !== null && draggedIndex !== toIndex) moveSection(draggedIndex, toIndex);
                    };
                });
            }

            document.getElementById('add-block-btn').onclick = function() {
                const select = document.getElementById('add-block-type');
                const type = select.value;
                if (!type) return;
                addSection(type, true);
                select.value = '';
            };

            document.getElementById('page-form').onsubmit = function() {
                if (typeof tinymce !== 'undefined') tinymce.triggerSave();
                syncSectionsInput();
            };

            renderSections();
            syncSectionsInput();
        });
    </script>
    @endpush
</x-app-layout>
