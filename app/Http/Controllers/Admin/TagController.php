<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\Language;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $tags = Tag::withCount('posts')->latest()->get();
        return view('admin.tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $languages = Language::where('is_active', true)->get();
        return view('admin.tags.create', compact('languages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $nameData = [];
        foreach ($data['name'] as $code => $name) {
            $nameData[$code] = $name;
        }
        $data['name'] = $nameData;

        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool) $data['is_active'];
        }

        if (empty($data['slug'])) {
            $defaultLang = Language::getDefault();
            $langCode = $defaultLang ? $defaultLang->code : 'en';
            $baseSlug = Tag::generateSlug($nameData, $langCode);

            $slug = $baseSlug;
            $counter = 1;
            while (Tag::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '_' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        Tag::create($data);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag): View
    {
        $languages = Language::where('is_active', true)->get();
        return view('admin.tags.edit', compact('tag', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $data = $request->validated();

        $nameData = [];
        foreach ($data['name'] as $code => $name) {
            $nameData[$code] = $name;
        }
        $data['name'] = $nameData;

        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool) $data['is_active'];
        }

        if (empty($data['slug'])) {
            $defaultLang = Language::getDefault();
            $langCode = $defaultLang ? $defaultLang->code : 'en';
            $baseSlug = Tag::generateSlug($nameData, $langCode);

            $slug = $baseSlug;
            $counter = 1;
            while (Tag::where('slug', $slug)->where('id', '!=', $tag->id)->exists()) {
                $slug = $baseSlug . '_' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        $tag->update($data);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();
        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}
