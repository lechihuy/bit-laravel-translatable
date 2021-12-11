<?php

namespace Bit\Translatable;

use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/translatable.php', 'translatable'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishing();

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register the publishing of the package.
     *
     * @return void
     */
    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/translatable.php' => base_path('config/translatable.php'),
        ], 'bit-laravel-translatable-config');
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
