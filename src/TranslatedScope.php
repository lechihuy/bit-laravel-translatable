<?php

namespace Bit\Translatable;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class TranslatedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder $builder
     * @param  Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->join($model->getTranslationTable(), function($join) use ($model) {
            $join->on(...$model->getTranslatedColumnConstraint())
                ->where($model->getLocaleKey(), App::currentLocale());
        });
    }
}
