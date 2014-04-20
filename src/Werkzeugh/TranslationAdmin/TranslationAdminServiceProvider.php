<?php namespace Werkzeugh\TranslationAdmin;

use Illuminate\Support\ServiceProvider;

use Waavi\Translation\Providers\LanguageProvider as LanguageProvider;
use Waavi\Translation\Providers\LanguageEntryProvider as LanguageEntryProvider;


class TranslationAdminServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

		$app=$this->app;

		$app->bind('Werkzeugh\TranslationAdmin\LanguageProvider', function($app) {
			return new LanguageProvider($app['config']['waavi/translation::language.model']);
		});


		$app->bind('Werkzeugh\TranslationAdmin\LanguageEntryProvider', function($app) {
			return new LanguageEntryProvider($app['config']['waavi/translation::language_entry.model']);
		});

		$this->app->register('Werkzeugh\AngularTranslation\AngularTranslationServiceProvider');

	 // { $x=$app; $x=htmlspecialchars(print_r($x,1));echo "\n<li>mwuits: <pre>$x</pre>"; }
		// die('register me');

	}

	public function boot()
	{
				$this->package('werkzeugh/translation-admin','mypkg');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
