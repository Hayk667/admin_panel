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
     * Display the menu order page (active pages only, tree with submenus).
     */
    public function index(): View
    {
        $pages = Page::where('is_active', true)
            ->topLevel()
            ->orderBy('menu_order')
            ->orderBy('id')
            ->with(['children' => function ($q) {
                $q->where('is_active', true)->orderBy('menu_order')->orderBy('id');
            }])
            ->get();

        return view('admin.menu.index', compact('pages'));
    }

    /**
     * Update menu order (drag and drop), supports nested submenus.
     * Expects: { "order": [ { "id": 1, "children": [ { "id": 2 }, ... ] }, ... ] }
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:pages,id',
            'order.*.children' => 'nullable|array',
            'order.*.children.*.id' => 'required|integer|exists:pages,id',
        ]);

        $order = $request->input('order');
        $position = 0;
        foreach ($order as $item) {
            $this->applyOrderRecursive($item, null, $position);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Apply menu_order and parent_id from nested order array.
     */
    private function applyOrderRecursive(array $item, ?int $parentId, int &$position): void
    {
        $pageId = (int) $item['id'];
        Page::where('id', $pageId)->update([
            'menu_order' => $position++,
            'parent_id' => $parentId,
        ]);
        $children = $item['children'] ?? [];
        foreach ($children as $child) {
            $this->applyOrderRecursive($child, $pageId, $position);
        }
    }
}
