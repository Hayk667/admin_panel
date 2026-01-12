<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Http\Requests\StoreLanguageRequest;
use App\Http\Requests\UpdateLanguageRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $languages = Language::latest()->get();
        return view('languages.index', compact('languages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('languages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLanguageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        // Handle is_active checkbox - if not set, set to false
        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool)$data['is_active'];
        }
        
        // If this is set as default, remove default from others
        // But only allow setting as default if the language is active
        if (isset($data['is_default']) && $data['is_default']) {
            if (!$data['is_active']) {
                return redirect()->route('languages.create')
                    ->withInput()
                    ->with('error', 'Cannot set an inactive language as default.');
            }
            Language::where('is_default', true)->update(['is_default' => false]);
        } else {
            $data['is_default'] = false;
        }

        Language::create($data);

        return redirect()->route('languages.index')
            ->with('success', 'Language created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Language $language): View
    {
        return view('languages.show', compact('language'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Language $language): View
    {
        return view('languages.edit', compact('language'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLanguageRequest $request, Language $language): RedirectResponse
    {
        $data = $request->validated();
        
        // Store original state before update
        $wasDefault = $language->is_default;
        $wasActive = $language->is_active;
        
        // Handle is_active checkbox - if not set, set to false
        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool)$data['is_active'];
        }
        
        // If this is set as default, remove default from others
        // But only allow setting as default if the language is active
        if (isset($data['is_default']) && $data['is_default']) {
            if (!$data['is_active']) {
                return redirect()->route('languages.index')
                    ->with('error', 'Cannot set an inactive language as default.');
            }
            Language::where('is_default', true)->where('id', '!=', $language->id)->update(['is_default' => false]);
        }

        $language->update($data);

        // If default language is being set to inactive, set next active language as default
        if ($wasDefault && $wasActive && !$data['is_active']) {
            // Find the next active language (excluding the current one)
            $nextActiveLanguage = Language::where('is_active', true)
                ->where('id', '!=', $language->id)
                ->orderBy('id', 'asc')
                ->first();
            
            if ($nextActiveLanguage) {
                // Set the next active language as default
                Language::where('is_default', true)->update(['is_default' => false]);
                $nextActiveLanguage->update(['is_default' => true]);
            } else {
                // No active languages found, remove default from current language
                $language->update(['is_default' => false]);
            }
        }

        return redirect()->route('languages.index')
            ->with('success', 'Language updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Language $language): RedirectResponse
    {
        // Check if user can delete languages
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete_language')) {
            abort(403, 'You do not have permission to delete languages.');
        }

        // Don't allow deletion of default language
        if ($language->is_default) {
            return redirect()->route('languages.index')
                ->with('error', 'Cannot delete the default language.');
        }

        $language->delete();

        return redirect()->route('languages.index')
            ->with('success', 'Language deleted successfully.');
    }
}

