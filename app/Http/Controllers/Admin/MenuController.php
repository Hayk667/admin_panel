<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Display the menu order page (active pages only).
     */
    public function index(): View
    {
        $pages = Page::where('is_active', true)
            ->orderBy('menu_order')
            ->orderBy('id')
            ->get();

        return view('admin.menu.index', compact('pages'));
    }

    /**
     * Update menu order (drag and drop).
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:pages,id',
        ]);

        $order = $request->input('order');

        foreach ($order as $position => $pageId) {
            Page::where('id', $pageId)->update(['menu_order' => $position]);
        }

        return response()->json(['success' => true]);
    }
}
