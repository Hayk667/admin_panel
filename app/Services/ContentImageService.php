<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ContentImageService
{
    /**
     * Prefix for our content images on public disk (we only delete these).
     */
    protected const STORAGE_PREFIX = 'images/content/';

    /**
     * Extract storage paths from HTML (img src pointing to our storage).
     * Returns paths relative to public disk, e.g. ["images/content/temp/abc.jpg"].
     */
    public static function extractStoragePathsFromHtml(string $html): array
    {
        if ($html === '' || $html === null) {
            return [];
        }
        $paths = [];
        if (preg_match_all('/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i', $html, $matches)) {
            foreach ($matches[1] as $url) {
                $path = self::urlToStoragePath($url);
                if ($path !== null) {
                    $paths[] = $path;
                }
            }
        }
        return array_unique($paths);
    }

    /**
     * Convert a URL to public storage path if it points to our storage.
     * Handles full URL (http(s)://.../storage/...) and relative (/storage/...).
     */
    public static function urlToStoragePath(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        // Relative: /storage/images/content/...
        if (preg_match('#^/storage/(.+)$#', $url, $m)) {
            $path = $m[1];
            return self::isContentImagePath($path) ? $path : null;
        }
        // Full URL: .../storage/images/content/...
        if (preg_match('#/storage/(.+)$#', $url, $m)) {
            $path = $m[1];
            // Remove query string if any
            $path = strtok($path, '?');
            return self::isContentImagePath($path) ? $path : null;
        }
        return null;
    }

    /**
     * Whether the path is under our content image prefix (so we only delete our own uploads).
     */
    protected static function isContentImagePath(string $path): bool
    {
        return str_starts_with($path, self::STORAGE_PREFIX);
    }

    /**
     * Extract all image paths from page sections (banner, slider, description, send_email_form).
     * When sections are deleted or images changed, orphaned paths are deleted by caller.
     *
     * @param  array<int, array{type: string, data?: array}>  $sections
     * @return array<string>
     */
    public static function extractImagePathsFromPageSections(array $sections): array
    {
        $paths = [];
        foreach ($sections as $section) {
            $type = $section['type'] ?? '';
            $data = $section['data'] ?? [];
            if ($type === 'banner' && !empty($data['image'])) {
                $path = self::urlToStoragePath($data['image']);
                if ($path !== null) {
                    $paths[] = $path;
                }
            }
            if ($type === 'slider' && !empty($data['slides']) && is_array($data['slides'])) {
                foreach ($data['slides'] as $slide) {
                    if (!empty($slide['image'])) {
                        $path = self::urlToStoragePath($slide['image']);
                        if ($path !== null) {
                            $paths[] = $path;
                        }
                    }
                }
            }
            if ($type === 'description' && !empty($data['content']) && is_array($data['content'])) {
                foreach ($data['content'] as $html) {
                    foreach (self::extractStoragePathsFromHtml((string) $html) as $p) {
                        $paths[] = $p;
                    }
                }
            }
            if ($type === 'send_email_form' && !empty($data['content']) && is_array($data['content'])) {
                foreach ($data['content'] as $html) {
                    foreach (self::extractStoragePathsFromHtml((string) $html) as $p) {
                        $paths[] = $p;
                    }
                }
            }
        }
        return array_unique($paths);
    }

    /**
     * Extract all image paths from post content (HTML per language).
     *
     * @param  array<string, string>  $content
     * @return array<string>
     */
    public static function extractImagePathsFromPostContent(array $content): array
    {
        $paths = [];
        foreach ($content as $html) {
            foreach (self::extractStoragePathsFromHtml((string) $html) as $p) {
                $paths[] = $p;
            }
        }
        return array_unique($paths);
    }

    /**
     * Delete paths from public disk. Skips non-existent and non-content paths.
     *
     * @param  array<string>  $paths
     */
    public static function deletePaths(array $paths): void
    {
        $disk = Storage::disk('public');
        foreach ($paths as $path) {
            if (!self::isContentImagePath($path)) {
                continue;
            }
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }

    /**
     * Get paths that are in $oldPaths but not in $newPaths (orphaned after edit).
     *
     * @param  array<string>  $oldPaths
     * @param  array<string>  $newPaths
     * @return array<string>
     */
    public static function orphanedPaths(array $oldPaths, array $newPaths): array
    {
        $newSet = array_flip($newPaths);
        $orphaned = [];
        foreach ($oldPaths as $path) {
            if (!isset($newSet[$path])) {
                $orphaned[] = $path;
            }
        }
        return $orphaned;
    }
}
