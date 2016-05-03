<?php

/**
 * Ensure this is only ran once.
 */
if (defined('CUTLASS_AUTOLOAD'))
{
    return;
}

define('CUTLASS_AUTOLOAD', microtime(true));

@require 'helpers.php';

/**
 * Get Cutlass.
 */
$cutlass = Cutlass\Framework\Application::getInstance();

/**
 * Load theme's cutlass.php file in theme directory
 */

$iterator = new DirectoryIterator(themes_directory());
$active_theme = ltrim(substr(get_template_directory(), strlen(themes_directory())));

foreach ($iterator as $directory)
{
    if ( ! $directory->valid() || $directory->isDot() || ! $directory->isDir())
    {
        continue;
    }

    $root = $directory->getPath() . '/' . $directory->getFilename();

    if ( ! file_exists($root . '/cutlass.config.php'))
    {
        continue;
    }

    $config = $cutlass->getThemeConfig($root);

    $theme = ltrim(substr($root, strlen(themes_directory())));

    wp_register_theme_activation_hook($theme, function () use ($cutlass, $config, $root)
    {
        if ( ! $cutlass->themeMatches($config))
        {
            $cutlass->themeMismatched($root);
        }

        $cutlass->themeMatched($root);
        $cutlass->loadTheme($config);
        $cutlass->activateTheme($root);
    });

    wp_register_theme_deactivation_hook($theme, function () use ($cutlass, $root)
    {
        $cutlass->deactivateTheme($root);
    });

    var_dump($active_theme);
    dd($theme);

    if ( $theme != $active_theme)
    {
        continue;
    }

    if ( ! $cutlass->themeMatches($config))
    {
        $cutlass->themeMismatched($root);

        continue;
    }

    $cutlass->themeMatched($root);

    @require_once $root.'/theme.php';

    $cutlass->loadTheme($config);
}

/**
 * Boot Cutlass.
 */
$cutlass->boot();
