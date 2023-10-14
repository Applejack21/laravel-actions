<?php

namespace Applejack21\LaravelActions;

use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use Applejack21\LaravelActions\Commands\CreateAction;

class ServiceProvider extends SupportServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->commands([
				CreateAction::class,
			]);
		}
	}
}
