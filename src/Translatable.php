<?php

namespace Bit\Translatable;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait Translatable
{
    use PerformsAttributes, PerformsRelationships;

    /**
     * Boot "translatable" for the model.
     *
     * @return void
     * @throws Exception
     */
    protected static function bootTranslatable(): void
    {
        static::addGlobalScope(new TranslatedScope);

        static::saving(function() {
            DB::beginTransaction();
        });

        static::saved(function($model) {
            try {
                $model->saveTranslations();

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();

                throw new Exception($e->getMessage());
            }
        });
    }

    /**
     * Save all translations of the model.
     *
     * @return void
     */
    protected function saveTranslations(): void
    {
        if (!$this->relationLoaded('translations')) return;

        foreach ($this->translations as $translation) {
            if ($translation->isDirty()) {
                if (! empty($connectionName = $this->getConnectionName())) {
                    $translation->setConnection($connectionName);
                }

                $translation->setAttribute($this->getTranslatedForeignKey(), $this->getKey());
                $translation->save();
            }
        }
    }

    /**
     * Delete the translations.
     *
     * @param  mixed|null $locales
     * @return void
     */
    public function deleteTranslations(string $locales = null): void
    {
        $locales = is_array($locales) ? $locales : func_get_args();
        $translations = $this->translations();

        if (count($locales))
            $translations->whereIn($this->getLocaleKey(), $locales);

        $translations->delete();

        if (!$this->translations->count())
            $this->delete();
    }

    /**
     * Get or create a new translation of the model.
     *
     * @param  string|null  $locale
     * @return Model
     */
    public function translationOrNew(string $locale = null): Model
    {
        return $this->translation($locale) ?? $this->createNewTranslation($locale);
    }

    /**
     * Create a new translation of the model.
     *
     * @param  string|null  $locale
     * @return Model
     */
    protected function createNewTranslation(string $locale = null): Model
    {
        $translation = $this->createNewTranslationInstance($locale);
        $this->translations->add($translation);

        return $translation;
    }

    /**
     * Get the translation of the model.
     *
     * @param  string|null  $locale
     * @return Model|null
     */
    public function translation(string $locale = null): ?Model
    {
        return $this->translations
            ->where($this->getLocaleKey(), $locale)
            ->first();
    }

    /**
     * Determine if the given translation exists.
     *
     * @param  string|null  $locale
     * @return bool
     */
    public function hasTranslation(string $locale = null): bool
    {
        return (bool) $this->translation($locale);
    }

    /**
     * Determine if any translations exists.
     *
     * @return bool
     */
    public function hasAnyTranslations(): bool
    {
        return (bool) $this->translations->count();
    }
}
