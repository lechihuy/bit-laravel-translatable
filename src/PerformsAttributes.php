<?php

namespace Bit\Translatable;

use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Str;

trait PerformsAttributes
{
    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        [$attribute, $locale] = $this->resolveTranslatedAttribute($key);

        if (! $this->isTranslatedAttribute($attribute)) {
            return parent::setAttribute($attribute, $value);
        }

        $this->translationOrNew($locale)->setAttribute($attribute, $value);

        return $this;
    }

    /**
     * Get a given attribute on the model.
     *
     * @param  string  $key
     * @return $this
     */
    public function getAttribute($key)
    {
        [$attribute, $locale] = $this->resolveTranslatedAttribute($key);

        if ($this->isTranslatedAttribute($attribute)) {
            if ($this->translation($locale) === null)
                return $this->getAttributeValue($attribute);

            if ($this->hasGetMutator($attribute)) 
                $this->attributes[$attribute] = $this->getTranslatedAttribute($attribute);

            return $this->getTranslatedAttribute($attribute);
        }

        return parent::getAttribute($key);
    }

    /**
     * Get a given translated attribute on the model.
     *
     * @param  string  $attribute
     * @return mixed
     */
    public function getTranslatedAttribute(string $attribute): mixed
    {
        return optional($this->translation())->getAttribute($attribute);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     * @return $this
     *
     * @throws MassAssignmentException
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $this->translationOrNew($key)->fill($value);
                unset($attributes[$key]);
            } else {
                [$attribute, $locale] = $this->resolveTranslatedAttribute($key);

                if ($this->isTranslatedAttribute($attribute)) {
                    $this->translationOrNew($locale)->fill([
                        $attribute => $value
                    ]);
                    unset($attributes[$key]);
                }
            }
        }

        return parent::fill($attributes);
    }

    /**
     * Resolve the given translated attribute.
     *
     * @param  string  $key
     * @return array
     */
    protected function resolveTranslatedAttribute(string $key): array
    {
        if (Str::contains($key, ':'))
            return explode(':', $key);

        return [$key, $this->currentLocale()];
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
}
