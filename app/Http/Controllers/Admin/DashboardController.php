<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Language;
use App\Models\Page;
use App\Models\Tag;
use App\Models\User;
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

        $pagesTotal = Page::count();
        $tagsTotal = Tag::count();
        $usersTotal = User::count();
        $recentPosts = Post::with('createdUser')->latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'postsTotal',
            'postsActive',
            'postsInactive',
            'categoriesTotal',
            'categoriesActive',
            'categoriesInactive',
            'languagesTotal',
            'languagesActive',
            'languagesInactive',
            'pagesTotal',
            'tagsTotal',
            'usersTotal',
            'recentPosts'
        ));
    }
}
