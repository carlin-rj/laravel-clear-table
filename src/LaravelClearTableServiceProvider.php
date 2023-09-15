<?php

namespace Mckue\LaravelClearTable;

use Illuminate\Support\ServiceProvider;
use Mckue\LaravelClearTable\Commands\ClearTableCommand;

class LaravelClearTableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearTableCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/config/clear-tables.php' => config_path('clear-tables.php'),
        ], 'clear-tables');

        $this->mergeConfigFrom(
            __DIR__.'/config/clear-tables.php', 'clear-tables'
        );
    }
}
