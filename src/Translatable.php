<?php

namespace Bit\Translatable;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

        static::saving(function($model) {
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
     * Get the specified translation of the model.
     * 
     * @param  Builder  $query
     * @param  string|null  $locale
     * @return Builder
     */
    public function scopeTranslated($query, $locale = null)
    {
        $locale = $locale ?? $this->currentLocale();
        
        $query->withoutGlobalScope(TranslatedScope::class)
            ->join($this->getTranslationTable(), function($join) use ($locale) {
                $join->on(...$this->getTranslatedColumnConstraint())
                    ->where($this->getLocaleKey(), $locale);
            });
    }

    /**
     * Get the current locale of the model.
     * 
     * @return string
     */
    public function currentLocale()
    {
        return App::currentLocale();
    }

    /**
     * Save all translations of the model.
     *
     * @return void
     */
    protected function saveTranslations(): void
    {
        foreach ($this->translations as $translation) {
            if ($translation->isDirty()) {
                if (! empty($connectionName = $this->getConnectionName()))
                    $translation->setConnection($connectionName);

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
    }

    /**
     * Restore the translations.
     *
     * @param  mixed|null $locales
     * @return void
     */
    public function restoreTranslations(string $locales = null): void
    {
        $locales = is_array($locales) ? $locales : func_get_args();
        $translations = $this->translations()->onlyTrashed();

        if (count($locales))
            $translations->whereIn($this->getLocaleKey(), $locales);

        $translations->restore();
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
        $locale = $locale ?: $this->currentLocale();
        $translation = $this->translations
            ->firstWhere($this->getLocaleKey(), $locale);

        if (!is_null($translation))
            $translation = $this->resolveTranslationInstance($translation, $locale);

        return $translation;
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

    /**
     * Determine if the translation model use soft deletes.
     * 
     * @return bool
     */ 
    public function translationHasSoftDeletes()
    {
        return property_exists($this, 'softDeletesTranslation') ? $this->softDeletesTranslation : false;
    }
}
