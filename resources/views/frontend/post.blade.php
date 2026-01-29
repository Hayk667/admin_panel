@extends('layouts.frontend')

@section('title', $post->getTitle($langCode))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex flex-col lg:flex-row gap-8">
        <div class="flex-1">
            <a href="{{ route('home') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mb-4 inline-block">← {{ __('Back to Home') }}</a>

            <article class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 lg:p-8">
                @if($post->image)
                    <div class="mb-6">
                        <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->getTitle($langCode) }}" class="w-full rounded-lg">
                    </div>
                @endif

                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ $post->getTitle($langCode) }}
                </h1>

                <div class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    @if($post->category)
                        <span class="mr-4">{{ __('Category') }}: {{ $post->category->getName($langCode) }}</span>
                    @endif
                    @if($post->published_at)
                        <span class="mr-4">{{ __('Published') }}: {{ $post->published_at->format('F d, Y') }}</span>
                    @endif
                    @if($post->tags->count() > 0)
                        <span>{{ __('Tags') }}:
                            @foreach($post->tags as $tag)
                                <span class="inline-block px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs mr-1">{{ $tag->name }}</span>
                            @endforeach
                        </span>
                    @endif
                </div>

                <div class="prose dark:prose-invert max-w-none mb-8">
                    {!! $post->getContent($langCode) !!}
                </div>

                <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                        <div class="flex items-center gap-2">
                            <button id="likeBtn" class="flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-800/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-800/30 transition {{ $hasLiked ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <svg id="heartIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" fill="{{ $hasLiked ? 'currentColor' : 'none' }}">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                </svg>
                                <span id="likeCount">{{ $post->likes ?? 0 }}</span>
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Rate') }}:</span>
                            <div class="flex gap-1" id="ratingStars">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" class="star-btn text-2xl transition {{ $hasRated && $i <= $userRating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }} {{ $hasRated ? 'opacity-50 cursor-not-allowed' : 'hover:text-yellow-400' }}" data-rating="{{ $i }}">★</button>
                                @endfor
                            </div>
                            <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">(<span id="currentRate">{{ number_format($post->rate ?? 0, 2) }}</span>)</span>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <aside class="lg:w-80 space-y-6">
            @if(isset($tags) && $tags->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 sticky top-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Tags') }}</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                {{ $tag->name }}
                                @if($tag->posts_count > 0)
                                    <span class="ml-1 text-gray-500 dark:text-gray-400">({{ $tag->posts_count }})</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 sticky top-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Recent Posts') }}</h2>
                @if($recentPosts->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentPosts as $recentPost)
                            <a href="{{ route('post.show', $recentPost->slug) }}" class="block p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                @if($recentPost->image)
                                    <img src="{{ asset('storage/' . $recentPost->image) }}" alt="{{ $recentPost->getTitle($langCode) }}" class="w-full h-32 object-cover rounded mb-2">
                                @endif
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">{{ Str::limit($recentPost->getTitle($langCode), 60) }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $recentPost->published_at ? $recentPost->published_at->format('M d, Y') : '' }}</p>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-sm">{{ __('No recent posts') }}</p>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeBtn = document.getElementById('likeBtn');
    const heartIcon = document.getElementById('heartIcon');
    const likeCount = document.getElementById('likeCount');
    const starButtons = document.querySelectorAll('.star-btn');
    const currentRate = document.getElementById('currentRate');
    let hasLiked = {{ $hasLiked ? 'true' : 'false' }};
    let hasRated = {{ $hasRated ? 'true' : 'false' }};
    let userRating = {{ $userRating ?? 0 }};

    likeBtn.addEventListener('click', function() {
        if (hasLiked) return;
        fetch('{{ route("post.like", $post->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                likeCount.textContent = data.likes;
                hasLiked = true;
                heartIcon.setAttribute('fill', 'currentColor');
                heartIcon.setAttribute('stroke', 'none');
                likeBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                alert(data.message || 'Unable to like this post');
            }
        })
        .catch(error => console.error('Error:', error));
    });

    starButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (hasRated) return;
            const rating = parseInt(this.getAttribute('data-rating'));
            fetch('{{ route("post.rate", $post->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ rating: rating })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    userRating = data.rating;
                    starButtons.forEach((btn, index) => {
                        if (index < userRating) {
                            btn.classList.add('text-yellow-400');
                            btn.classList.remove('text-gray-300', 'dark:text-gray-600');
                        }
                    });
                    currentRate.textContent = parseFloat(data.rate).toFixed(2);
                    hasRated = true;
                    starButtons.forEach(btn => btn.classList.add('opacity-50', 'cursor-not-allowed'));
                } else {
                    alert(data.message || 'Unable to rate this post');
                }
            })
            .catch(error => console.error('Error:', error));
        });
        button.addEventListener('mouseenter', function() {
            if (hasRated) return;
            const rating = parseInt(this.getAttribute('data-rating'));
            starButtons.forEach((btn, index) => {
                if (index < rating) {
                    btn.classList.add('text-yellow-400');
                    btn.classList.remove('text-gray-300', 'dark:text-gray-600');
                } else {
                    btn.classList.remove('text-yellow-400');
                    btn.classList.add('text-gray-300', 'dark:text-gray-600');
                }
            });
        });
        button.addEventListener('mouseleave', function() {
            if (hasRated) return;
            starButtons.forEach((btn, index) => {
                btn.classList.remove('text-yellow-400');
                btn.classList.add('text-gray-300', 'dark:text-gray-600');
            });
        });
    });
});
</script>
@endpush
