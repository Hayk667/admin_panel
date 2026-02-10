<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Language;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Services\ContentImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $pages = Page::orderBy('menu_order')->orderBy('id')->get();
        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $languages = Language::where('is_active', true)->get();
        return view('admin.pages.create', compact('languages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $sections = $data['sections'] ?? [];

        $firstTitleSection = collect($sections)->firstWhere('type', 'title');
        $titleData = $firstTitleSection['data']['title'] ?? [];
        $data['title'] = is_array($titleData) ? $titleData : [];
        $data['content'] = [];

        $defaultLang = Language::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'en';
        $baseSlug = Page::generateSlug($data['title'], $langCode);
        $slug = $baseSlug ?: 'page';
        $counter = 1;
        while (Page::where('slug', $slug)->exists()) {
            $slug = ($baseSlug ?: 'page') . '_' . $counter;
            $counter++;
        }
        $data['slug'] = $slug;

        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['menu_order'] = (int) (Page::max('menu_order') ?? 0) + 1;

        Page::create($data);

        return redirect()->route('admin.pages.index')
            ->with('success', __('Page created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page): View
    {
        $languages = Language::where('is_active', true)->get();
        return view('admin.pages.edit', compact('page', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $data = $request->validated();
        $sections = $data['sections'] ?? [];

        $firstTitleSection = collect($sections)->firstWhere('type', 'title');
        $titleData = $firstTitleSection['data']['title'] ?? [];
        $data['title'] = is_array($titleData) ? $titleData : [];
        $data['content'] = [];

        $defaultLang = Language::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'en';
        $baseSlug = Page::generateSlug($data['title'], $langCode);
        $slug = $baseSlug ?: $page->slug;
        $counter = 1;
        while (Page::where('slug', $slug)->where('id', '!=', $page->id)->exists()) {
            $slug = ($baseSlug ?: 'page') . '_' . $counter;
            $counter++;
        }
        $data['slug'] = $slug;

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        // Delete images that were in old sections but are no longer in new sections (changed/removed)
        $oldSections = $page->sections ?? [];
        $newSections = $data['sections'] ?? [];
        if (is_array($oldSections) && is_array($newSections)) {
            $oldPaths = ContentImageService::extractImagePathsFromPageSections($oldSections);
            $newPaths = ContentImageService::extractImagePathsFromPageSections($newSections);
            $orphaned = ContentImageService::orphanedPaths($oldPaths, $newPaths);
            ContentImageService::deletePaths($orphaned);
        }

        $page->update($data);

        return redirect()->route('admin.pages.index')
            ->with('success', __('Page updated successfully.'));
    }

    /**
     * Remove the specified resource from storage (soft delete).
     * Images are kept on soft delete and only removed when the page is force-deleted.
     */
    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', __('Page deleted successfully.'));
    }
}
