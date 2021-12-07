<?php

namespace Bit\Translatable;

use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register the commands of the package.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands([
            //
        ]);
    }
}
