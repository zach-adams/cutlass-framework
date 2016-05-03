<?php namespace Cutlass\Framework;

use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class Cache {

	/**
	 * @param $view Factory|BladeCompiler
	 * @param $path string
	 *
	 * @return bool
	 */
	public static function is_expired($view, $path)
	{

		/**
		 * If we're debugging we'll set all cache files to expired
		 */
		if(defined('WP_DEBUG') && WP_DEBUG) {
			return true;
		}

		/**
		 * If the cache file doesn't exist or isn't readable then it's considered expired
		 */
		if(!is_readable($path)) {
			return true;
		}

		/**
		 * Use Blade Compiler to make sure it can read the cache file alright
		 * @var $blade_compiler BladeCompiler
		 */
		$blade_compiler = cutlass('blade.compiler');

		if($blade_compiler->isExpired($view->getPath())) {
			return true;
		}

		return false;

	}


	/**
	 * Return the default Cache directory
	 *
	 * @return string
	 */
	public static function path()
	{

		$cache_path = upload_directory() . '/.cutlass-cache';

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

		Cache::setPermissions($path);

		$views = glob($path . '/' . ltrim($glob, '/'));

		if(false === $views) {
			throw new InvalidArgumentException(sprintf('The glob %s is not valid.', self::valueToString($glob)));
		}

		array_map('unlink', glob($path."/**/*.php"));

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
		Assert::allDigits($permissions, '$permissions must be a valid directory permissions setting');
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
