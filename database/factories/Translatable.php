<?php

namespace Bit\Translatable\Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

trait Translatable
{
    /**
     * Indicate that the model will be translated to the specified locales.
     *
     * @param  mixed  $locales,...
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function translated($locales)
    {
        $locales = is_array($locales) ? $locales : func_get_args();

        return $this->state(function (array $attributes) use ($locales) {
            return collect($locales)
                ->mapWithKeys(fn($locale) => [$locale => $this->definition()])
                ->all();
        });
    }
}