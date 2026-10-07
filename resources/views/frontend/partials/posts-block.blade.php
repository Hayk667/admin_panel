@php
    $flag = function ($value, $fallback = false) {
        if ($value === true || $value === 1 || $value === '1') {
            return true;
        }
        if ($value === false || $value === 0 || $value === '0') {
            return false;
        }

        return $fallback;
    };

    $limit = max(1, min(100, (int) ($data['limit'] ?? 15)));
    $order = ($data['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
    $orderBy = ($data['order_by'] ?? 'date') === 'title' ? 'title' : 'date';
    $columns = max(1, min(6, (int) ($data['columns'] ?? 3)));
    $gap = max(0, min(80, (int) ($data['gap'] ?? 30)));
    $textLength = max(1, min(2000, (int) ($data['text_length'] ?? 125)));
    $showAuthor = $flag($data['show_author'] ?? false);
    $showDate = $flag($data['show_date'] ?? true, true);
    $showCategories = $flag($data['show_categories'] ?? false);
    $showTags = $flag($data['show_tags'] ?? false);
    $showText = $flag($data['show_text'] ?? true, true);
    $showReadMore = $flag($data['show_read_more'] ?? false);
    $openNewTab = $flag($data['open_new_tab'] ?? false);
    $useFilter = $flag($data['use_filter'] ?? true, true);
    $filterUsing = ($data['filter_using'] ?? 'categories') === 'tags' ? 'tags' : 'categories';

    $categoryIds = collect($data['category_ids'] ?? ['all'])->map(fn ($id) => (string) $id)->filter()->values();
    $tagIds = collect($data['tag_ids'] ?? ['all'])->map(fn ($id) => (string) $id)->filter()->values();
    $allCategories = $categoryIds->isEmpty() || $categoryIds->contains('all');
    $allTags = $tagIds->isEmpty() || $tagIds->contains('all');

    $postsQuery = \App\Models\Post::with(['category', 'tags', 'createdUser'])->where('is_active', true);
    if (! $allCategories) {
        $postsQuery->whereIn('category_id', $categoryIds->filter(fn ($id) => ctype_digit($id))->all());
    }
    if (! $allTags) {
        $selectedTags = $tagIds->filter(fn ($id) => ctype_digit($id))->all();
        $postsQuery->whereHas('tags', fn ($query) => $query->whereIn('tags.id', $selectedTags));
    }
    if ($orderBy === 'title') {
        $code = preg_match('/^[A-Za-z0-9_-]{2,10}$/', (string) $langCode) ? $langCode : 'en';
        $postsQuery->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"{$code}\"')) {$order}");
    } else {
        $postsQuery->orderByRaw('COALESCE(published_at, created_at) '.$order);
    }
    $blockPosts = $postsQuery->limit($limit)->get();

    $filters = collect();
    if ($useFilter && $filterUsing === 'categories') {
        $filters = $allCategories
            ? $blockPosts->pluck('category')->filter()->unique('id')->values()
            : \App\Models\Category::whereIn('id', $categoryIds->filter(fn ($id) => ctype_digit($id))->all())->orderBy('slug')->get();
    } elseif ($useFilter) {
        $filters = $allTags
            ? $blockPosts->flatMap->tags->unique('id')->values()
            : \App\Models\Tag::whereIn('id', $tagIds->filter(fn ($id) => ctype_digit($id))->all())->orderBy('slug')->get();
    }
@endphp

@once
    <style>
        .posts-block-grid {
            display: grid;
            grid-template-columns: 1fr;
        }
        @media (min-width: 768px) {
            .posts-block-grid {
                grid-template-columns: repeat(var(--posts-columns, 3), minmax(0, 1fr));
            }
        }
        .posts-filter-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 88px;
            padding: 8px 18px;
            border: 2px solid #f5a000;
            background: #f5a000;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1.2;
            cursor: pointer;
        }
        .posts-filter-btn.is-active {
            background: #fff;
            color: #f5a000;
        }
    </style>
@endonce

<section class="posts-block px-6 sm:px-8 py-8" data-posts-block data-filter-kind="{{ $filterUsing }}">
    @if ($useFilter && $filters->isNotEmpty())
        <div class="flex flex-wrap justify-center gap-3 mb-8">
            <button type="button" class="posts-filter-btn is-active" data-filter="all">{{ __('All') }}</button>
            @foreach ($filters as $filter)
                <button type="button" class="posts-filter-btn" data-filter="{{ $filter->id }}">{{ $filter->getName($langCode) }}</button>
            @endforeach
        </div>
    @endif

    @if ($blockPosts->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No posts found.') }}</p>
    @else
        <div class="posts-block-grid" style="--posts-columns: {{ $columns }}; gap: {{ $gap }}px;">
            @foreach ($blockPosts as $post)
                @php
                    $title = $post->getTitle($langCode);
                    $plain = html_entity_decode(strip_tags((string) $post->getContent($langCode)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $plain = trim(preg_replace('/\s+/u', ' ', str_replace("\xc2\xa0", ' ', $plain)));
                    if (mb_strlen($plain) > $textLength) {
                        $plain = rtrim(mb_substr($plain, 0, $textLength)).'...';
                    }
                    $image = $post->thumbnail ?: $post->image;
                    $imageUrl = null;
                    if ($image) {
                        $imageUrl = (str_starts_with($image, 'http') || str_starts_with($image, '/')) ? $image : asset('storage/'.$image);
                    }
                    $date = $post->published_at ?: $post->created_at;
                    $postUrl = route('post.show', $post->slug);
                    $linkTarget = $openNewTab ? '_blank' : null;
                @endphp
                <article class="min-w-0" data-post-card data-category="{{ $post->category_id }}" data-tags="{{ $post->tags->pluck('id')->implode(',') }}">
                    <a href="{{ $postUrl }}" @if ($linkTarget) target="{{ $linkTarget }}" rel="noopener" @endif class="block">
                        @if ($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $title }}" class="w-full aspect-[16/10] object-cover bg-gray-100">
                        @else
                            <div class="w-full aspect-[16/10] bg-gray-100"></div>
                        @endif
                    </a>
                    <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white leading-snug">
                        <a href="{{ $postUrl }}" @if ($linkTarget) target="{{ $linkTarget }}" rel="noopener" @endif class="hover:text-gray-700 dark:hover:text-gray-300">{{ $title !== '' ? $title : __('(no title)') }}</a>
                    </h3>
                    @if ($showDate && $date)
                        <p class="mt-1 text-sm italic text-gray-500 dark:text-gray-400">{{ $date->format('F j, Y') }}</p>
                    @endif
                    @if ($showAuthor && $post->createdUser)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $post->createdUser->name }}</p>
                    @endif
                    @if ($showCategories && $post->category)
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $post->category->getName($langCode) }}</p>
                    @endif
                    @if ($showTags && $post->tags->isNotEmpty())
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $post->tags->map(fn ($tag) => $tag->getName($langCode))->implode(', ') }}</p>
                    @endif
                    @if ($showText && $plain !== '')
                        <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-300">{{ $plain }}</p>
                    @endif
                    @if ($showReadMore)
                        <a href="{{ $postUrl }}" @if ($linkTarget) target="{{ $linkTarget }}" rel="noopener" @endif class="inline-block mt-3 text-sm font-semibold text-gray-900 dark:text-white underline">{{ __('Read more') }}</a>
                    @endif
                </article>
            @endforeach
        </div>
        <p class="posts-filter-empty mt-6 text-sm text-gray-500 dark:text-gray-400" data-filter-empty hidden>{{ __('No posts found.') }}</p>
    @endif
</section>
