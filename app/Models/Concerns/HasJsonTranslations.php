<?php

namespace App\Models\Concerns;

use App\Models\Language;

trait HasJsonTranslations
{
    /**
     * Whether this record has non-empty translated content for a language.
     */
    public function hasTranslation(?string $languageCode = null): bool
    {
        $languageCode = $languageCode ?? Language::currentCode();

        foreach (['title', 'content', 'name', 'sections'] as $attribute) {
            if (!array_key_exists($attribute, $this->getAttributes()) && !isset($this->{$attribute})) {
                continue;
            }

            if ($this->jsonHasLanguageValue($this->{$attribute}, $languageCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recursively detect a non-empty string stored under a language code key.
     */
    protected function jsonHasLanguageValue(mixed $payload, string $languageCode): bool
    {
        if (!is_array($payload)) {
            return false;
        }

        if (array_key_exists($languageCode, $payload)) {
            $value = $payload[$languageCode];
            if (is_string($value) && trim(strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'))) !== '') {
                return true;
            }
        }

        foreach ($payload as $value) {
            if (is_array($value) && $this->jsonHasLanguageValue($value, $languageCode)) {
                return true;
            }
        }

        return false;
    }
}
