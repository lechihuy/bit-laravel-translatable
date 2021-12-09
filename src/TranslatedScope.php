<?php

namespace Bit\Translatable;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class TranslatedScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = ['DeleteTranslations', 'WhereLocale'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder $builder
     * @param  Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // $builder->join($model->getTranslationTable(), function($join) use ($model) {
        //     $join->on(...$model->getTranslatedColumnConstraint())
        //         ->where($model->getLocaleKey(), App::currentLocale());
        // });
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the "delete translations" extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addDeleteTranslations(Builder $builder)
    {
        $builder->macro('deleteTranslations', function (Builder $builder, $locales = null) {
            $locales = is_array($locales) ? $locales : [$locales];
            $model = $builder->getModel();
            $models = $builder->getModels();

            $model->createNewTranslationInstance()
                ->whereIn($model->getLocaleKey(), $locales)
                ->whereIn(
                    $model->getTranslatedForeignKey(), 
                    collect($models)->pluck('id')->all()
                )->delete();
        });
    }

    /**
     * Add the "where locale" extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addWhereLocale(Builder $builder)
    {
        $builder->macro('whereLocale', function (Builder $builder, $locale) {
            $model = $builder->getModel();

            $builder->where($model->getLocaleKey(), $locales);
        });
    }
}
