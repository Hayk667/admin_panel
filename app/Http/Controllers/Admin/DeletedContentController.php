<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeletedContentController extends Controller
{
    /**
     * Allowed soft-deletable types (route key => [Model, label]).
     */
    protected const TYPES = [
        'post'     => [Post::class, 'Post'],
        'page'     => [Page::class, 'Page'],
        'language' => [Language::class, 'Language'],
        'user'     => [User::class, 'User'],
    ];

    /**
     * Display a listing of all soft-deleted content.
     */
    public function index(): View
    {
        $defaultLang = Language::withTrashed()->where('is_default', true)->first()
            ?? Language::withTrashed()->where('is_active', true)->first();
        $langCode = $defaultLang ? $defaultLang->code : 'en';

        $items = collect();

        foreach (self::TYPES as $typeKey => [$modelClass, $typeLabel]) {
            $modelClass::onlyTrashed()
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->each(function ($model) use ($typeKey, $typeLabel, $langCode, &$items) {
                    $items->push((object)[
                        'id'         => $model->getKey(),
                        'type'       => $typeKey,
                        'type_label' => $typeLabel,
                        'title'      => $this->getTitleForModel($model, $typeKey, $langCode),
                        'deleted_at' => $model->deleted_at,
                    ]);
                });
        }

        $items = $items->sortByDesc('deleted_at')->values();

        return view('admin.deleted-content.index', compact('items'));
    }

    /**
     * Restore a soft-deleted item.
     */
    public function restore(Request $request, string $type, string $id): RedirectResponse
    {
        $model = $this->resolveTrashedModel($type, $id);
        if (!$model) {
            return redirect()->route('admin.deleted-content.index')
                ->with('error', __('Record not found or already restored.'));
        }

        $model->restore();

        return redirect()->route('admin.deleted-content.index')
            ->with('success', __('Content restored successfully.'));
    }

    /**
     * Permanently delete (force delete) an item.
     */
    public function forceDelete(Request $request, string $type, string $id): RedirectResponse
    {
        $model = $this->resolveTrashedModel($type, $id);
        if (!$model) {
            return redirect()->route('admin.deleted-content.index')
                ->with('error', __('Record not found or already deleted.'));
        }

        $model->forceDelete();

        return redirect()->route('admin.deleted-content.index')
            ->with('success', __('Content permanently deleted.'));
    }

    /**
     * Resolve a trashed model by type and id.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function resolveTrashedModel(string $type, string $id)
    {
        if (!isset(self::TYPES[$type])) {
            return null;
        }

        $modelClass = self::TYPES[$type][0];
        $model = $modelClass::onlyTrashed()->find($id);

        return $model;
    }

    /**
     * Get display title for a trashed model.
     */
    protected function getTitleForModel($model, string $typeKey, string $langCode): string
    {
        if ($typeKey === 'language') {
            return $model->name ?? (string) $model->code;
        }
        if ($typeKey === 'user') {
            return $model->name ?? (string) $model->email;
        }
        if (method_exists($model, 'getTitle')) {
            $title = $model->getTitle($langCode);
            return $title !== '' ? $title : (string) ($model->slug ?? $model->getKey());
        }
        return (string) $model->getKey();
    }
}
