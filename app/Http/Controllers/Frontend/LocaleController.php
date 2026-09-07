<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LocaleController extends Controller
{
    /**
     * Switch the frontend content language and stay on the current page when translated.
     */
    public function switch(Request $request, string $code): RedirectResponse
    {
        $language = Language::where('code', $code)->where('is_active', true)->firstOrFail();

        $request->session()->put(Language::FRONTEND_LOCALE_KEY, $language->code);

        $redirectTo = $this->resolveRedirectUrl($request, $language->code);

        return redirect()
            ->to($redirectTo)
            ->cookie(Language::FRONTEND_LOCALE_KEY, $language->code, 60 * 24 * 365);
    }

    private function resolveRedirectUrl(Request $request, string $code): string
    {
        $home = url('/');
        $candidate = $request->query('redirect') ?: $request->headers->get('referer') ?: $home;

        if (!$this->isSameApplicationUrl($candidate)) {
            return $home;
        }

        $route = $this->matchFrontendRoute($candidate);

        if (!$route) {
            return $candidate;
        }

        $name = $route->getName();

        if ($name === 'locale.switch') {
            return $home;
        }

        if ($name === 'post.show') {
            $post = Post::where('slug', $route->parameter('slug'))
                ->where('is_active', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->first();

            return ($post && $post->hasTranslation($code)) ? $candidate : $home;
        }

        if ($name === 'page.show' || $name === 'page.send-message') {
            $page = Page::where('slug', $route->parameter('slug'))
                ->where('is_active', true)
                ->first();

            return ($page && $page->hasTranslation($code)) ? $candidate : $home;
        }

        return $candidate;
    }

    private function isSameApplicationUrl(string $url): bool
    {
        $appRoot = rtrim($this->requestRoot(), '/');
        $parts = parse_url($url);

        if ($parts === false) {
            return false;
        }

        if (!isset($parts['host'])) {
            return str_starts_with($url, '/') && !str_starts_with($url, '//');
        }

        $candidateRoot = ($parts['scheme'] ?? parse_url($appRoot, PHP_URL_SCHEME) ?? 'http').'://'.$parts['host'];
        if (isset($parts['port'])) {
            $candidateRoot .= ':'.$parts['port'];
        }

        return $candidateRoot === $appRoot;
    }

    private function requestRoot(): string
    {
        return rtrim(request()->root(), '/');
    }

    private function matchFrontendRoute(string $url): ?\Illuminate\Routing\Route
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '/';
        $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';

        try {
            return Route::getRoutes()->match(Request::create($path.$query, 'GET'));
        } catch (NotFoundHttpException) {
            return null;
        } catch (\Throwable) {
            return null;
        }
    }
}
