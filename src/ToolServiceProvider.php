<?php

namespace Anditsung\LabelCreator;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Anditsung\LabelCreator\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'label-creator');

        $this->loadMigrationsFrom(__DIR__ . '/database');
        //$this->publishDatabase();

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            //
        });
    }

    private function getDatabases()
    {
        return [
            "2019_12_11_092054_create_label_creator_label_types_table.php",
        ];
    }

    private function publishDatabase()
    {
        $databases = $this->getDatabases();

        foreach($databases as $database) {
            $this->publishes([
                __DIR__ . '/database/' . $database => database_path() . '/migrations/' . $database,
            ]);
        }
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/label-creator')
                ->group(__DIR__.'/../routes/api.php');

        Route::middleware(['nova', Authorize::class])
                ->prefix('label-creator')
                ->group(__DIR__ . '/../routes/web.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
