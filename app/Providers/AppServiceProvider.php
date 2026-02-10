<?php

namespace App\Providers;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.frontend', function ($view) {
            $menuPages = Page::where('is_active', true)
                ->topLevel()
                ->orderBy('menu_order')
                ->orderBy('id')
                ->with(['children' => function ($q) {
                    $q->where('is_active', true)->orderBy('menu_order')->orderBy('id');
                }])
                ->get();
            $defaultLang = Language::getDefault();
            $langCode = $defaultLang ? $defaultLang->code : 'en';
            $view->with(compact('menuPages', 'langCode'));
        });
    }
}
