<?php

namespace Bit\Translatable;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait PerformsRelationships
{
    /**
     * Get the translation table name of the model.
     *
     * @return string
     */
    public function getTranslationTable(): string
    {
        return property_exists($this, 'translationTable')
            ? $this->translationTable
            : Str::of($this->getTable())->snake()->singular().'_translations';
    }

    /**
     * Get the translated foreign key name of the model.
     *
     * @return string
     */
    public function getTranslatedForeignKey(): string
    {
        return property_exists($this, 'translatedForeignKey')
            ? $this->translatedForeignKey
            : $this->getForeignKey();
    }

    /**
     * Qualify the given translated column of the translated model.
     *
     * @param  string  $column
     * @return string
     */
    public function qualifyTranslatedColumn(string $column): string
    {
        return $this->getTranslationTable().'.'.$column;
    }

    /**
     * Get the column constraint for the translated join of the model.
     *
     * @return array
     */
    public function getTranslatedColumnConstraint(): array
    {
        return [
            $this->qualifyColumn($this->getKeyName()),
            '=',
            $this->qualifyTranslatedColumn($this->getTranslatedForeignKey())
        ];
    }

    /**
     * Get locale key name of the model.
     *
     * @return string
     */
    public function getLocaleKey(): string
    {
        return property_exists($this, 'localeKey') 
            ? $this->localeKey 
            : config('translatable.locale_key', 'locale');
    }

    /**
     * Get all translations that belong to the model.
     *
     * @return HasMany
     */
    public function translations(): HasMany
    {
        $instance = $this->createNewTranslationInstance();
        $foreignKey = $this->getTranslatedForeignKey();
        $localKey = $this->getKeyName();

        return $this->newHasMany(
            $instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey
        );
    }

    /**
     * Determine if the given attribute is a translated attribute.
     *
     * @param  string  $attribute
     * @return bool
     */
    public function isTranslatedAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->getTranslatedAttributes());
    }

    /**
     * Get all translated attributes of the model.
     *
     * @return array
     */
    public function getTranslatedAttributes(): array
    {
        if (property_exists($this, 'translatedAttributes'))
            return $this->translatedAttributes;

        return Cache::rememberForever($this->getTable().'_translated_attributes', function () {
            $exceptedColumns = [
                $this->getKeyName(), 
            ];
     
            return collect(Schema::getColumnListing($this->getTranslationTable()))
                ->diff($exceptedColumns)
                ->all();
        });
    }

    /**
     * Create a new translation model's instance.
     *
     * @param  string|null  $locale
     * @return Model
     */
    public function createNewTranslationInstance(string $locale = null): Model
    {
        $locale = $locale ?? $this->currentLocale();
        $instance = $this->newRelatedInstance($this->getTranslatedModelName());

        return $this->resolveTranslationInstance($instance, $locale);
    }

    /**
     * Resolve the translation model's instance.
     * 
     * @param  Model  $instance
     * @param  string|null  $locale
     * @return Model
     */
    public function resolveTranslationInstance(Model $instance, string $locale = null): Model
    {
        $instance->setTable($this->getTranslationTable());
        $instance->fillable(array_values(array_intersect(
            $this->getFillable(),
            $this->getTranslatedAttributes()
        )));
        $instance->setKeyName($this->getTranslationKey());

        if (!is_null($locale))
            $instance->setAttribute($this->getLocaleKey(), $locale);
        
        return $instance;
    }

    /**
     * Get the translation key of the model.
     * 
     * @return string
     */
    public function getTranslationKey()
    {
        return property_exists($this, 'translationKey') 
            ? $this->translationKey
            : config('translatable.translation_key', 'translation_id'); 
    }

    /**
     * Get the translated model name of the model.
     *
     * @return string
     */
    public function getTranslatedModelName(): string
    {
        return property_exists($this, 'translatedModel')
            ? $this->translatedModel
            : ($this->translationHasSoftDeletes() 
                ? 'Bit\\Translatable\\Models\\SoftDeletesTranslation'
                : 'Bit\\Translatable\\Models\\Translation'
            );
    }
}
