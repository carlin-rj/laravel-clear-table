<?php

namespace Mckue\LaravelClearTable;

use Mckue\LaravelClearTable\Commands\ClearTableCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearTableCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/config/clear-table.php' => config_path('clear-table.php'),
        ], 'clear-table');

        $this->mergeConfigFrom(
            __DIR__.'/config/clear-table.php', 'clear-table'
        );
    }
}
