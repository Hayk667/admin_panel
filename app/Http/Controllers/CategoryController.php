<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Language;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categories = Category::withCount('posts')->latest()->get();
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $languages = Language::where('is_active', true)->get();
        return view('categories.create', compact('languages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        // Convert name array to JSON format
        $nameData = [];
        foreach ($data['name'] as $code => $name) {
            $nameData[$code] = $name;
        }
        $data['name'] = $nameData;

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
            $baseSlug = Category::generateSlug($nameData, $langCode);
            
            // Ensure slug is unique
            $slug = $baseSlug;
            $counter = 1;
            while (Category::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '_' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        Category::create($data);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        $category->load('posts');
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        $languages = Language::where('is_active', true)->get();
        return view('categories.edit', compact('category', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        
        // Convert name array to JSON format
        $nameData = [];
        foreach ($data['name'] as $code => $name) {
            $nameData[$code] = $name;
        }
        $data['name'] = $nameData;

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
            $baseSlug = Category::generateSlug($nameData, $langCode);
            
            // Ensure slug is unique (excluding current category)
            $slug = $baseSlug;
            $counter = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $baseSlug . '_' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Check if user can delete categories
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete_category')) {
            abort(403, 'You do not have permission to delete categories.');
        }

        // Posts will have category_id set to null automatically via foreign key constraint
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}

