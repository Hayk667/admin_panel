@extends('layouts.frontend')

@section('title', $page->getTitle($langCode) ?: $page->slug)

@section('content')
@php
    $pageSections = $page->sections ?? [];
    if (!is_array($pageSections)) {
        $pageSections = [];
    }
    $firstIsBanner = isset($pageSections[0]) && ($pageSections[0]['type'] ?? '') === 'banner';
    $firstIsSlider = isset($pageSections[0]) && ($pageSections[0]['type'] ?? '') === 'slider';
    $firstIsHero = $firstIsBanner || $firstIsSlider;
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 {{ $firstIsHero ? 'pt-0 pb-12' : 'py-12' }}">
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-800 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-800 dark:text-red-200">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $sections = $page->sections ?? [];
        if (!is_array($sections)) {
            $sections = [];
        }
        $hasFullWidthBanner = collect($sections)->contains(fn($s) => ($s['type'] ?? '') === 'banner' && !empty($s['data']['full_width']));
        $hasFullWidthSlider = collect($sections)->contains(fn($s) => ($s['type'] ?? '') === 'slider' && !empty($s['data']['full_width']));
        $hasFullWidthHero = $hasFullWidthBanner || $hasFullWidthSlider;
    @endphp

    <article class="bg-white dark:bg-gray-800 rounded-lg shadow-xl {{ $hasFullWidthHero ? 'overflow-visible' : 'overflow-hidden' }}">
        @forelse ($sections as $section)
            @php
                $type = $section['type'] ?? 'title';
                $data = $section['data'] ?? [];
            @endphp

            @if ($type === 'banner')
                @php $bannerFullWidth = !empty($data['full_width']); @endphp
                @if ($bannerFullWidth)
                    <div class="w-screen relative left-1/2 -translate-x-1/2 max-w-none">
                @endif
                <section class="relative overflow-hidden bg-gray-200 dark:bg-gray-700 {{ $bannerFullWidth ? 'w-screen' : '' }}">
                    @if (!empty($data['image']) && (str_starts_with($data['image'], 'http') || str_starts_with($data['image'], '/')))
                        <img src="{{ $data['image'] }}" alt="{{ $data['title'][$langCode] ?? '' }}" class="w-full h-64 sm:h-80 object-cover">
                    @endif
                    <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-4 bg-black/40">
                        <h1 class="text-3xl sm:text-4xl font-bold text-white drop-shadow-lg">
                            {{ $data['title'][$langCode] ?? $data['title']['en'] ?? '' }}
                        </h1>
                        @if (!empty($data['subtitle'][$langCode] ?? $data['subtitle']['en'] ?? ''))
                            <p class="mt-2 text-lg text-white/90 drop-shadow">
                                {{ $data['subtitle'][$langCode] ?? $data['subtitle']['en'] ?? '' }}
                            </p>
                        @endif
                    </div>
                </section>
                @if ($bannerFullWidth)
                    </div>
                @endif
            @elseif ($type === 'slider')
                @php
                    $sliderFullWidth = !empty($data['full_width']);
                    $slides = $data['slides'] ?? [];
                    $slidesWithImage = array_values(array_filter($slides, fn($s) => !empty($s['image']) && (str_starts_with($s['image'], 'http') || str_starts_with($s['image'], '/'))));
                    $sliderId = 'slider-' . $loop->index;
                @endphp
                @if (count($slidesWithImage) > 0)
                    @if ($sliderFullWidth)
                        <div class="w-screen relative left-1/2 -translate-x-1/2 max-w-none">
                    @endif
                    <section class="slider-section relative overflow-hidden bg-gray-200 dark:bg-gray-700 {{ $sliderFullWidth ? 'w-screen' : '' }}" data-slider-id="{{ $sliderId }}" data-slide-count="{{ count($slidesWithImage) }}">
                        <div class="slider-track flex transition-transform duration-500 ease-out" style="width: {{ count($slidesWithImage) * 100 }}%">
                            @foreach ($slidesWithImage as $slideIdx => $slide)
                                <div class="slider-slide flex-shrink-0 w-full relative" style="width: {{ 100 / count($slidesWithImage) }}%">
                                    <img src="{{ $slide['image'] }}" alt="{{ $slide['title'][$langCode] ?? $slide['title']['en'] ?? '' }}" class="w-full h-64 sm:h-80 object-cover">
                                    <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-4 bg-black/40">
                                        <h2 class="text-2xl sm:text-3xl font-bold text-white drop-shadow-lg">
                                            {{ $slide['title'][$langCode] ?? $slide['title']['en'] ?? '' }}
                                        </h2>
                                        @if (!empty($slide['subtitle'][$langCode] ?? $slide['subtitle']['en'] ?? ''))
                                            <p class="mt-2 text-base text-white/90 drop-shadow">
                                                {{ $slide['subtitle'][$langCode] ?? $slide['subtitle']['en'] ?? '' }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if (count($slidesWithImage) > 1)
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                                @foreach (array_keys($slidesWithImage) as $idx)
                                    <button type="button" class="slider-dot w-2.5 h-2.5 rounded-full {{ $idx === 0 ? 'bg-white' : 'bg-white/50 hover:bg-white/75' }}" data-slider-id="{{ $sliderId }}" data-index="{{ $idx }}" aria-label="Slide {{ $idx + 1 }}"></button>
                                @endforeach
                            </div>
                            <button type="button" class="slider-prev absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-black/30 text-white hover:bg-black/50 z-10" data-slider-id="{{ $sliderId }}" aria-label="Previous">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" class="slider-next absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-black/30 text-white hover:bg-black/50 z-10" data-slider-id="{{ $sliderId }}" aria-label="Next">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        @endif
                    </section>
                    @if ($sliderFullWidth)
                        </div>
                    @endif
                @endif
            @elseif ($type === 'title')
                <section class="px-6 sm:px-8 py-6 border-b border-gray-100 dark:border-gray-700">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $data['title'][$langCode] ?? $data['title']['en'] ?? '' }}
                    </h1>
                </section>
            @elseif ($type === 'description')
                <section class="px-6 sm:px-8 py-6 border-b border-gray-100 dark:border-gray-700">
                    <div class="prose dark:prose-invert max-w-none break-words min-w-0">
                        {!! html_entity_decode((string) ($data['content'][$langCode] ?? $data['content']['en'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}
                    </div>
                </section>
            @elseif ($type === 'inputs')
                <section class="px-6 sm:px-8 py-6 border-b border-gray-100 dark:border-gray-700">
                    @php $inputs = $data['inputs'] ?? []; @endphp
                    @if (count($inputs) > 0)
                        <div class="space-y-4 max-w-xl">
                            @foreach ($inputs as $input)
                                @php
                                    $label = $input['label'][$langCode] ?? $input['label']['en'] ?? '';
                                    $placeholder = $input['placeholder'][$langCode] ?? $input['placeholder']['en'] ?? '';
                                    $inputType = $input['input_type'] ?? 'text';
                                @endphp
                                <div>
                                    @if ($label)
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $label }}</label>
                                    @endif
                                    <input type="{{ $inputType }}" placeholder="{{ $placeholder }}"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @elseif ($type === 'send_email_form')
                <section class="px-6 sm:px-8 py-6 border-b border-gray-100 dark:border-gray-700">
                    @php
                        $formTitle = $data['form_title'][$langCode] ?? $data['form_title']['en'] ?? __('Contact');
                        $emailLabel = $data['email_label'][$langCode] ?? $data['email_label']['en'] ?? __('Email');
                        $contentLabel = $data['content_label'][$langCode] ?? $data['content_label']['en'] ?? __('Message');
                        $sendButton = $data['send_button'][$langCode] ?? $data['send_button']['en'] ?? __('Send');
                        $formContent = $data['content'][$langCode] ?? $data['content']['en'] ?? '';
                    @endphp
                    <div class="max-w-xl">
                        @if ($formTitle)
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ $formTitle }}</h2>
                        @endif
                        @if ($formContent)
                            <div class="prose dark:prose-invert text-sm text-gray-600 dark:text-gray-400 mb-4 break-words min-w-0">
                                {!! html_entity_decode((string) $formContent, ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}
                            </div>
                        @endif
                        <form action="{{ route('page.send-message', $page->slug) }}" method="post" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="contact-email-{{ $loop->iteration }}">{{ $emailLabel }}</label>
                                <input type="email" name="email" id="contact-email-{{ $loop->iteration }}" required
                                    value="{{ old('email') }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3 @error('email') border-red-500 dark:border-red-400 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="contact-message-{{ $loop->iteration }}">{{ $contentLabel }}</label>
                                <textarea name="message" id="contact-message-{{ $loop->iteration }}" rows="4" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3 @error('message') border-red-500 dark:border-red-400 @enderror">{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                {{ $sendButton }}
                            </button>
                        </form>
                    </div>
                </section>
            @endif
        @empty
            {{-- No sections: show page title and a message --}}
            <div class="px-6 sm:px-8 py-12">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ $page->getTitle($langCode) ?: $page->slug }}
                </h1>
                <p class="text-gray-500 dark:text-gray-400">{{ __('This page has no content yet.') }}</p>
            </div>
        @endforelse
    </article>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.slider-section').forEach(function(section) {
        const id = section.dataset.sliderId;
        const count = parseInt(section.dataset.slideCount, 10) || 1;
        if (count <= 1) return;
        const track = section.querySelector('.slider-track');
        const dots = section.querySelectorAll('.slider-dot[data-slider-id="' + id + '"]');
        const prev = section.querySelector('.slider-prev[data-slider-id="' + id + '"]');
        const next = section.querySelector('.slider-next[data-slider-id="' + id + '"]');
        let index = 0;
        function goTo(i) {
            index = ((i % count) + count) % count;
            if (track) track.style.transform = 'translateX(-' + (index * 100 / count) + '%)';
            dots.forEach(function(d, j) {
                d.classList.toggle('bg-white', j === index);
                d.classList.toggle('bg-white/50', j !== index);
            });
        }
        dots.forEach(function(d, i) {
            d.addEventListener('click', function() { goTo(i); });
        });
        if (prev) prev.addEventListener('click', function() { goTo(index - 1); });
        if (next) next.addEventListener('click', function() { goTo(index + 1); });
        setInterval(function() { goTo(index + 1); }, 5000);
    });
});
</script>
@endpush
@endsection
