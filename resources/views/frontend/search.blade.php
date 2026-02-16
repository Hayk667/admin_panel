@extends('layouts.frontend')

@section('title', $term !== '' ? __('Search results for :term', ['term' => $term]) : __('Search'))

@section('content')
<div class="bg-dots-darker bg-dots-lighter bg-center bg-gray-100 dark:bg-gray-900 min-h-[60vh] selection:bg-red-500 selection:text-white">
    <div class="max-w-7xl mx-auto p-6 lg:p-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            {{ $term !== '' ? __('Search results for ":term"', ['term' => e($term)]) : __('Search') }}
        </h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">
            {{ __('Searches in post titles and content in all languages.') }}
        </p>

        <div class="mt-6">
            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    @foreach($posts as $post)
                        <a href="{{ route('post.show', $post->slug) }}" class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                            <div>
                                <div class="h-16 w-16 bg-red-50 dark:bg-red-800/20 rounded-full overflow-hidden">
                                    @if($post->image)
                                        <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->getTitle($langCode) }}" class="w-16 h-16 object-cover rounded-full">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-7 h-7 stroke-red-500">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <h2 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">{{ $post->getTitle($langCode) }}</h2>

                                @if($post->tags->count() > 0)
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($post->tags as $tag)
                                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $tag->name }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                    {{ Str::limit(strip_tags($post->getContent($langCode)), 150) }}
                                </p>
                            </div>

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="self-center shrink-0 stroke-red-500 w-6 h-6 mx-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                            </svg>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8 flex justify-center">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ $term !== '' ? __('No posts found for ":term".', ['term' => e($term)]) : __('Enter a search term above.') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
