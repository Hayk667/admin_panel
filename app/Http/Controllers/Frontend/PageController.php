<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPageMessageRequest;
use App\Mail\PageContactMessage;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display a single page by slug (active pages only).
     */
    public function show(string $slug): View
    {
        $defaultLang = Language::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'en';

        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('frontend.page', compact('page', 'langCode'));
    }

    /**
     * Send contact form message from a page (Send Email block).
     */
    public function sendMessage(SendPageMessageRequest $request, string $slug): RedirectResponse
    {
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $defaultLang = Language::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'en';
        $pageTitle = $page->getTitle($langCode) ?: $page->slug;

        $toAddress = config('mail.contact_to') ?: config('mail.from.address');

        try {
            Mail::to($toAddress)->send(new PageContactMessage(
                senderEmail: $request->validated('email'),
                messageBody: $request->validated('message'),
                pageTitle: $pageTitle
            ));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('page.show', $slug)
                ->with('error', __('Failed to send your message. Please try again later.'));
        }

        return redirect()->route('page.show', $slug)
            ->with('success', __('Your message has been sent. We will get back to you soon.'));
    }
}
