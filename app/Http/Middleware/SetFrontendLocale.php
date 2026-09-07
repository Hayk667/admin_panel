<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetFrontendLocale
{
    /**
     * Apply the visitor's chosen frontend language for this request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $code = $this->resolveLocale($request);
        app()->setLocale($code);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $default = Language::getDefault()?->code ?? config('app.locale', 'en');
        $candidate = $request->session()->get(Language::FRONTEND_LOCALE_KEY)
            ?? $request->cookie(Language::FRONTEND_LOCALE_KEY);

        if (!is_string($candidate) || $candidate === '') {
            return $default;
        }

        $isActive = Language::where('code', $candidate)->where('is_active', true)->exists();

        if (!$isActive) {
            return $default;
        }

        if (!$request->session()->has(Language::FRONTEND_LOCALE_KEY)) {
            $request->session()->put(Language::FRONTEND_LOCALE_KEY, $candidate);
        }

        return $candidate;
    }
}
