<?php

namespace App\Models\Concerns;

/**
 * Bilingual columns: a model lists its fields in $translatable (e.g. ['name']),
 * and $model->name returns name_bn or name_en for the current locale,
 * falling back to the other language when that one is empty.
 */
trait HasTranslations
{
    public function translate(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $other = $locale === 'bn' ? 'en' : 'bn';

        return $this->getAttribute("{$field}_{$locale}")
            ?: $this->getAttribute("{$field}_{$other}");
    }

    public function getAttribute($key)
    {
        if (in_array($key, $this->translatable, true)) {
            return $this->translate($key);
        }

        return parent::getAttribute($key);
    }
}
