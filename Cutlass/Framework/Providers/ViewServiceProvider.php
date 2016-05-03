<?php namespace Cutlass\Framework\Providers;

use Cutlass\Framework\Application;
use Illuminate\Events\Dispatcher;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewServiceProvider as ServiceProvider;

class ViewServiceProvider extends ServiceProvider {


	/**
	 * Register the Blade engine implementation.
	 *
	 * @todo    Spruce this up
	 * @param  \Illuminate\View\Engines\EngineResolver  $resolver
	 * @return void
	 */
	public function registerBladeEngine($resolver)
	{
		$app = $this->app;

		$this->app->bind('blade.options', function ()
		{

			$blade_cache = content_directory() . '/blade-cache';

			foreach ($this->app->getThemes() as $theme)
			{
				$blade_cache = $theme->getBasePath() . '/storage/framework/views/';
			}

			if(!wp_is_writable($blade_cache)) {
				wp_mkdir_p($blade_cache);
			}

			return [
				'view.compiled' => $blade_cache,
			];
		});

		// The Compiler engine requires an instance of the CompilerInterface, which in
		// this case will be the Blade compiler, so we'll first create the compiler
		// instance to pass into the engine so it can compile the views properly.
		$app->singleton('blade.compiler', function ($app) {
			$cache = $app['blade.options']['view.compiled'];

			return new BladeCompiler($app['files'], $cache);
		});

		$resolver->register('blade', function () use ($app) {
			return new CompilerEngine($app['blade.compiler']);
		});
	}

	/**
	 * Register the view finder implementation.
	 *
	 * @return void
	 */
	public function registerViewFinder()
	{
		$this->app->bind('view.finder', function ($app) {
			$paths = [];

			foreach ($this->app->getThemes() as $theme)
			{
				$paths[] = $theme->getBasePath() . '/resources/views';
			}

			return new FileViewFinder($app['files'], $paths);
		});
	}

	/**
	 * Register the view environment.
	 *
	 * @return void
	 */
	public function registerFactory()
	{
		$this->app->singleton('view', function ($app) {
			// Next we need to grab the engine resolver instance that will be used by the
			// environment. The resolver will be used by an environment to get each of
			// the various engine implementations such as plain PHP or Blade engine.
			$resolver = $app['view.engine.resolver'];

			$finder = $app['view.finder'];

			$env = new Factory($resolver, $finder, new Dispatcher());

			// We will also set the container instance on this view environment since the
			// view composers may be classes registered in the container, which allows
			// for great testable, flexible composers for the application developer.
			$env->setContainer($app);

			$env->share('app', $app);

			return $env;
		});
	}
}