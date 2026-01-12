<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Language;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $posts = Post::with(['category', 'createdUser', 'updatedUser'])->latest()->get();
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Category::where('is_active', true)->get();
        $languages = Language::where('is_active', true)->get();
        return view('posts.create', compact('categories', 'languages'));
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

        Post::create($data);

        return redirect()->route('posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): View
    {
        $post->load(['category', 'createdUser', 'updatedUser']);
        return view('posts.show', compact('post'));
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
        $languages = Language::where('is_active', true)->get();
        $post->load('category');
        return view('posts.edit', compact('post', 'categories', 'languages'));
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

        $post->update($data);

        return redirect()->route('posts.index')
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

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}

