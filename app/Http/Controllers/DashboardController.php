<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics.
     */
    public function index(): View
    {
        // Posts statistics
        $postsTotal = Post::count();
        $postsActive = Post::where('is_active', true)->count();
        $postsInactive = Post::where('is_active', false)->count();

        // Categories statistics
        $categoriesTotal = Category::count();
        $categoriesActive = Category::where('is_active', true)->count();
        $categoriesInactive = Category::where('is_active', false)->count();

        // Languages statistics
        $languagesTotal = Language::count();
        $languagesActive = Language::where('is_active', true)->count();
        $languagesInactive = Language::where('is_active', false)->count();

        return view('dashboard', compact(
            'postsTotal',
            'postsActive',
            'postsInactive',
            'categoriesTotal',
            'categoriesActive',
            'categoriesInactive',
            'languagesTotal',
            'languagesActive',
            'languagesInactive'
        ));
    }
}

