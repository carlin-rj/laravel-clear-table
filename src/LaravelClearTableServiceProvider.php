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
			$this->getConfigFile() => config_path('clear-tables.php'),
		], 'config');

    }
	public function register(): void
	{
		$this->mergeConfigFrom(
			$this->getConfigFile(),
			'clear-tables'
		);
	}

    protected function getConfigFile(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'clear-tables.php';
    }
}
