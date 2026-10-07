<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Language;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Services\ImageService;
use App\Services\ContentImageService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        if (! in_array($status, ['all', 'mine', 'published', 'drafts'], true)) {
            $status = 'all';
        }

        $order = $request->query('order') === 'asc' ? 'asc' : 'desc';
        $search = trim((string) $request->query('s', ''));
        $month = $request->query('m');
        $categoryId = $request->query('category');
        $authorId = $request->query('author');
        $tagId = $request->query('tag');

        $languageFilter = null;
        if ($request->filled('lang')) {
            $candidate = Language::where('code', $request->query('lang'))->first();
            if ($candidate && preg_match('/^[A-Za-z0-9_-]{2,10}$/', $candidate->code)) {
                $languageFilter = $candidate;
            }
        }

        $query = Post::with(['category', 'createdUser', 'tags']);

        if ($status === 'mine') {
            $query->where('created_user_id', auth()->id());
        } elseif ($status === 'published') {
            $query->where('is_active', true);
        } elseif ($status === 'drafts') {
            $query->where('is_active', false);
        }

        if ($languageFilter) {
            $query->whereNotNull('title->'.$languageFilter->code)
                ->where('title->'.$languageFilter->code, '!=', '');
        }

        if ($categoryId === 'none') {
            $query->whereNull('category_id');
        } elseif (is_string($categoryId) && ctype_digit($categoryId)) {
            $query->where('category_id', $categoryId);
        } else {
            $categoryId = null;
        }

        if (is_string($authorId) && ctype_digit($authorId)) {
            $query->where('created_user_id', $authorId);
        } else {
            $authorId = null;
        }

        if (is_string($tagId) && ctype_digit($tagId)) {
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        } else {
            $tagId = null;
        }

        if (is_string($month) && preg_match('/^\d{6}$/', $month)) {
            $query->whereRaw("DATE_FORMAT(COALESCE(published_at, created_at), '%Y%m') = ?", [$month]);
        } else {
            $month = null;
        }

        if ($search !== '') {
            $query->search($search);
        }

        $query->orderByRaw('COALESCE(published_at, created_at) '.($order === 'asc' ? 'asc' : 'desc'));

        $posts = $query->paginate(20)->withQueryString();

        $counts = [
            'all' => Post::count(),
            'mine' => Post::where('created_user_id', auth()->id())->count(),
            'published' => Post::where('is_active', true)->count(),
            'drafts' => Post::where('is_active', false)->count(),
        ];

        $languages = Language::orderByDesc('is_active')->orderBy('name')->get();
        $titleRows = Post::query()->get(['title']);
        $languageCounts = [];
        foreach ($languages as $language) {
            $languageCounts[$language->code] = $titleRows->filter(function ($row) use ($language) {
                $value = is_array($row->title) ? ($row->title[$language->code] ?? null) : null;

                return is_string($value) && trim($value) !== '';
            })->count();
        }

        $categories = Category::withCount('posts')->orderBy('slug')->get();
        $months = Post::query()
            ->selectRaw("DATE_FORMAT(COALESCE(published_at, created_at), '%Y%m') as ym")
            ->selectRaw("ANY_VALUE(DATE_FORMAT(COALESCE(published_at, created_at), '%M %Y')) as label")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('ym')
            ->orderByDesc('ym')
            ->get();

        $defaultLang = Language::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'en';

        $filters = array_filter([
            'status' => $status !== 'all' ? $status : null,
            'lang' => $languageFilter?->code,
            'category' => $categoryId,
            'm' => $month,
            's' => $search !== '' ? $search : null,
            'order' => $order === 'asc' ? 'asc' : null,
            'author' => $authorId,
            'tag' => $tagId,
        ], fn ($value) => $value !== null && $value !== '');

        return view('admin.posts.index', compact(
            'posts',
            'counts',
            'languages',
            'languageCounts',
            'categories',
            'months',
            'langCode',
            'status',
            'order',
            'search',
            'month',
            'categoryId',
            'languageFilter',
            'filters'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Category::where('is_active', true)->get();
        $tags = Tag::where('is_active', true)->orderBy('slug')->get();
        $languages = Language::where('is_active', true)->get();
        return view('admin.posts.create', compact('categories', 'tags', 'languages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // Verify file is actually an image using getimagesize
            if ($file->isValid()) {
                $imageInfo = @getimagesize($file->getRealPath());
                if ($imageInfo === false) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['image' => 'The file must be a valid image file.']);
                }
            }

            $imageData = ImageService::uploadAndCrop($file);
            $data['image'] = $imageData['image'];
            $data['thumbnail'] = $imageData['thumbnail'];
        }

        // Convert title and content arrays to JSON format
        $titleData = [];
        $contentData = [];
        foreach ($data['title'] as $code => $title) {
            $titleData[$code] = $title;
        }
        foreach ($data['content'] as $code => $content) {
            $contentData[$code] = $content;
        }
        $data['title'] = $titleData;
        $data['content'] = $contentData;

        // Handle is_active checkbox - if not set, set to false
        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool)$data['is_active'];
        }

        // Generate slug automatically if not provided
        if (empty($data['slug'])) {
            $defaultLang = Language::getDefault();
            $langCode = $defaultLang ? $defaultLang->code : 'en';
            $baseSlug = Post::generateSlug($titleData, $langCode);

            // Ensure slug is unique
            $slug = $baseSlug;
            $counter = 1;
            while (Post::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '_' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        // Set the user who created the post
        $data['created_user_id'] = auth()->id();

        $tagIds = array_values(array_filter($data['tags'] ?? [], fn ($id) => $id !== '' && (int) $id > 0));
        unset($data['tags']);

        $post = Post::create($data);
        $post->tags()->sync($tagIds);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): View
    {
        $post->load(['category', 'createdUser', 'updatedUser']);
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): View
    {
        // Check if user can edit this post
        if (!auth()->user()->canEditPost($post)) {
            abort(403, 'You do not have permission to edit this post.');
        }

        $categories = Category::where('is_active', true)->get();
        $tags = Tag::where('is_active', true)->orderBy('slug')->get();
        $languages = Language::where('is_active', true)->get();
        $post->load(['category', 'tags']);
        return view('admin.posts.edit', compact('post', 'categories', 'tags', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        // Check if user can edit this post
        if (!auth()->user()->canEditPost($post)) {
            abort(403, 'You do not have permission to edit this post.');
        }

        $data = $request->validated();

        // Handle image upload - delete old image if new one is uploaded
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // Verify file is actually an image using getimagesize
            if ($file->isValid()) {
                $imageInfo = @getimagesize($file->getRealPath());
                if ($imageInfo === false) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['image' => 'The file must be a valid image file.']);
                }
            }

            // Delete old images
            if ($post->image) {
                ImageService::delete($post->image, $post->thumbnail);
            }

            // Upload new image
            $imageData = ImageService::uploadAndCrop($file);
            $data['image'] = $imageData['image'];
            $data['thumbnail'] = $imageData['thumbnail'];
        } else {
            // Keep existing images
            unset($data['image']);
        }

        // Convert title and content arrays to JSON format
        $titleData = [];
        $contentData = [];
        foreach ($data['title'] as $code => $title) {
            $titleData[$code] = $title;
        }
        foreach ($data['content'] as $code => $content) {
            $contentData[$code] = $content;
        }
        $data['title'] = $titleData;
        $data['content'] = $contentData;

        // Handle is_active checkbox - if not set, set to false
        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool)$data['is_active'];
        }

        // Generate slug automatically if not provided
        if (empty($data['slug'])) {
            $defaultLang = Language::getDefault();
            $langCode = $defaultLang ? $defaultLang->code : 'en';
            $baseSlug = Post::generateSlug($titleData, $langCode);

            // Ensure slug is unique (excluding current post)
            $slug = $baseSlug;
            $counter = 1;
            while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $baseSlug . '_' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        // Set the user who updated the post
        $data['updated_user_id'] = auth()->id();

        $tagIds = array_values(array_filter($data['tags'] ?? [], fn ($id) => $id !== '' && (int) $id > 0));
        unset($data['tags']);

        // Delete content images that were removed or replaced in the editor
        $oldContent = $post->content ?? [];
        $newContent = $contentData;
        if (is_array($oldContent) && is_array($newContent)) {
            $oldPaths = ContentImageService::extractImagePathsFromPostContent($oldContent);
            $newPaths = ContentImageService::extractImagePathsFromPostContent($newContent);
            $orphaned = ContentImageService::orphanedPaths($oldPaths, $newPaths);
            ContentImageService::deletePaths($orphaned);
        }

        $post->update($data);
        $post->tags()->sync($tagIds);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        // Check if user can delete this post
        if (!auth()->user()->canDeletePost($post)) {
            abort(403, 'You do not have permission to delete this post.');
        }

        // Images will be deleted automatically via model boot method
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Apply a bulk action to the selected posts.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $action = $request->input('action');
        $ids = array_slice(array_values(array_filter((array) $request->input('ids', []), function ($id) {
            return is_string($id) && $id !== '';
        })), 0, 100);

        if (! in_array($action, ['trash', 'publish', 'draft'], true) || $ids === []) {
            return redirect()->back()->with('error', 'Select an action and at least one post.');
        }

        $posts = Post::whereIn('id', $ids)->get();
        $updated = 0;

        foreach ($posts as $post) {
            if ($action === 'trash') {
                if (! auth()->user()->canDeletePost($post)) {
                    continue;
                }
                $post->delete();
                $updated++;
                continue;
            }

            if (! auth()->user()->canEditPost($post)) {
                continue;
            }

            if ($action === 'publish') {
                $post->is_active = true;
                if (! $post->published_at) {
                    $post->published_at = now()->toDateString();
                }
            } else {
                $post->is_active = false;
            }

            $post->updated_user_id = auth()->id();
            $post->save();
            $updated++;
        }

        $skipped = $posts->count() - $updated;

        if ($updated === 0) {
            return redirect()->back()->with('error', 'You do not have permission to change the selected posts.');
        }

        if ($action === 'trash') {
            $message = $updated === 1 ? '1 post moved to the trash.' : $updated.' posts moved to the trash.';
        } else {
            $message = $updated === 1 ? '1 post updated.' : $updated.' posts updated.';
        }

        if ($skipped > 0) {
            $message .= ' '.$skipped.' skipped.';
        }

        return redirect()->back()->with('success', $message);
    }
}
