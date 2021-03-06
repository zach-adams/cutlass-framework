<?php namespace Cutlass\Framework\Providers;

use Cutlass\Framework\Application;
use Cutlass\Framework\Cache;
use Illuminate\Events\Dispatcher;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewServiceProvider as ServiceProvider;

class ViewServiceProvider extends ServiceProvider {

	/**
	 * @var Application $app
	 */
	protected $app;

	/**
	 * Register the Blade engine implementation.
	 *
	 * @param  \Illuminate\View\Engines\EngineResolver  $resolver
	 * @return void
	 */
	public function registerBladeEngine($resolver)
	{
		$app = $this->app;

		$this->app->bind('blade.options', function ()
		{

			$blade_cache = Cache::path();

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

			$views_folder = rtrim($this->app->getTheme()->getBasePath(), '/') . '/' . ltrim($this->app->getTheme()->config('views'), '/');

			$views_folder = [$views_folder];

			return new FileViewFinder($app['files'], $views_folder);

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