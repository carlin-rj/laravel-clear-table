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

        $this->mergeConfigFrom(
            __DIR__.'/config/clear-tables.php', 'clear-tables'
        );
    }

	/**
	 * @return string
	 */
	protected function getConfigFile(): string
	{
		return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'clear-tables.php';
	}
}
