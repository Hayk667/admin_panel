<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Language;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $pages = Page::latest()->get();
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

        $page->update($data);

        return redirect()->route('admin.pages.index')
            ->with('success', __('Page updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', __('Page deleted successfully.'));
    }
}
