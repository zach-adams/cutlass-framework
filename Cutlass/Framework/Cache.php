<?php namespace Cutlass\Framework;

use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class Cache {

	/**
	 * @param $view \Illuminate\View\Factory|\Illuminate\View\Compilers\BladeCompiler
	 * @param $path string|null
	 *
	 * @return bool
	 */
	public static function isExpired($view, $path = null)
	{

		if(false === self::isCacheEnabled()) {
			return true;
		}

		/**
		 * If we're debugging we'll set all cache files to expired
		 */
		if(defined('WP_DEBUG') && WP_DEBUG) {
			return true;
		}

		/**
		 * If the cache file doesn't exist or isn't readable then it's considered expired
		 */
		if(!is_null($path) && !is_readable($path)) {
			return true;
		}

		/**
		 * Use Blade Compiler to make sure it can read the cache file alright
		 * @var $blade_compiler \Illuminate\View\Compilers\BladeCompiler
		 */
		$blade_compiler = cutlass('blade.compiler');

		if($blade_compiler->isExpired($view->getPath())) {
			return true;
		}

		return false;

	}


	/**
	 * @param $view \Illuminate\View\Factory|\Illuminate\View\Compilers\BladeCompiler
	 *
	 * @return bool
	 */
	public static function deleteCachedFile($view)
	{

		if(!$view instanceof Factory && !$view instanceof BladeCompiler) {
			throw new InvalidArgumentException(sprintf('The object %s is not an instace of \Illuminate\View\Compilers\BladeCompiler or \Illuminate\View\Factory.', self::valueToString($view)));
		}

		return self::deleteFile($view->getPath());

	}


	/**
	 * Deletes a given file
	 *
	 * @param string|\Illuminate\View\Compilers\BladeCompiler $file
	 *
	 * @return mixed
	 */
	public static function deleteFile($file)
	{

		if($file instanceof BladeCompiler) {
			$file = $file->getPath();
		}

		Assert::file($file, '$file must be a Blade Compiler or a valid file.');
		Assert::writable($file, '$file must be writable.');

		$file = realpath($file);

		return unlink($file);

	}


	/**
	 * Checks to see if the Cache is enabled
	 *
	 * @return bool
	 */
	public static function isCacheEnabled()
	{

		/**
		 * @var Application $app
		 */
		$app = cutlass();

		/**
		 * @var \Cutlass\Framework\Base\Theme $theme
		 */
		$theme = $app->getTheme();

		$blade_cache_enabled = $theme->config('blade_cache_enabled');

		Assert::boolean($blade_cache_enabled, '$blade_cache_enabled must be a boolean value.');

		return $blade_cache_enabled;

	}


	/**
	 * Return the default Cache directory
	 *
	 * @return string
	 */
	public static function path()
	{

		/**
		 * @var Application $app
		 */
		$app = cutlass();

		$blade_cache = $app->getTheme()->config('blade_cache');

		$cache_path = rtrim($app->getTheme()->getBasePath(), '/') . '/' . ltrim($blade_cache, '/');

		if(!wp_is_writable($cache_path)) {
			wp_mkdir_p($cache_path);
		}

		return realpath($cache_path);

	}


	/**
	 * Delete all cached views
	 *
	 * @param $path string
	 * @param $glob string
	 *
	 * @return void
	 */
	public static function deleteAllViews($path = null, $glob = "/**/*.php")
	{

		if(null === $path) {
			$path = Cache::path();
		}

		Assert::directory($path, '$path must be a valid directory.');
		Assert::readable($path, '$path must be a valid directory.');

		dd($path . '/' . ltrim($glob, '/'));

		Cache::setPermissions($path);

		$views = glob($path . '/' . ltrim($glob, '/'));

		if(false === $views) {
			throw new InvalidArgumentException(sprintf('The glob %s is not valid.', self::valueToString($glob)));
		}

		array_map('unlink', $views);

	}


	/**
	 * Sets given permissions on given path
	 *
	 * @param string|null $path
	 * @param int  $permissions
	 *
	 * @return void
	 */
	public static function setPermissions($path = null, $permissions = 775)
	{

		if(null === $path) {
			$path = Cache::path();
		}

		Assert::directory($path, '$path must be a valid directory.');
		Assert::readable($path, '$path must be a valid directory.');
		Assert::integer($permissions, '$permissions must be a valid directory permissions setting');
		Assert::length($permissions, 3, '$permissions must be a valid directory permissions setting');

		chmod($path, octdec($permissions));

	}


	/**
	 * Creates a directory given a path
	 *
	 * @param string|null $path
	 *
	 * @return void
	 */
	public static function createDirectory($path = null)
	{

		if(null === $path) {
			$path = Cache::path();
		}

		wp_mkdir_p($path);

	}


	/**
	 * Deletes a given directory recursively
	 *
	 * @param string|null $path
	 *
	 * @return void
	 */
	public static function deleteDirectory($path = null)
	{

		if(null === $path) {
			$path = Cache::path();
		}

		Assert::directory($path);

		if (substr($path, strlen($path) - 1, 1) != '/') {
			$path .= '/';
		}

		$files = glob($path . '*', GLOB_MARK);
		foreach ($files as $file) {
			if (is_dir($file)) {
				self::deleteDirectory($file);
			} else {
				unlink($file);
			}
		}

		rmdir($path);

	}


	/**
	 * Converts a var value to string
	 *
	 * @param $value
	 *
	 * @return string
	 */
	protected static function valueToString($value)
	{
		if (null === $value) {
			return 'null';
		}

		if (true === $value) {
			return 'true';
		}

		if (false === $value) {
			return 'false';
		}

		if (is_array($value)) {
			return 'array';
		}

		if (is_object($value)) {
			return get_class($value);
		}

		if (is_resource($value)) {
			return 'resource';
		}

		if (is_string($value)) {
			return '"'.$value.'"';
		}

		return (string) $value;
	}

}
