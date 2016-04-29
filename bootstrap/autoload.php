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
 * Load the WP plugin system.
 */
if (array_search(ABSPATH . 'wp-admin/includes/plugin.php', get_included_files()) === false)
{
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * Get Cutlass.
 */
$cutlass = Cutlass\Framework\Application::getInstance();

/**
 * Load all cutlass.php files in plugin roots.
 */
$iterator = new DirectoryIterator(plugin_directory());


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

    $config = $cutlass->getPluginConfig($root);

    $plugin = substr($root . '/plugin.php', strlen(plugin_directory()));
    $plugin = ltrim($plugin, '/');

    register_activation_hook($plugin, function () use ($cutlass, $config, $root)
    {
        if ( ! $cutlass->pluginMatches($config))
        {
            $cutlass->pluginMismatched($root);
        }

        $cutlass->pluginMatched($root);
        $cutlass->loadPlugin($config);
        $cutlass->activatePlugin($root);
    });

    register_deactivation_hook($plugin, function () use ($cutlass, $root)
    {
        $cutlass->deactivatePlugin($root);
    });

    // Ugly hack to make the install hook work correctly
    // as WP doesn't allow closures to be passed here
    register_uninstall_hook($plugin, create_function('', 'cutlass()->deletePlugin(\'' . $root . '\');'));

    if ( ! is_plugin_active($plugin))
    {
        continue;
    }

    if ( ! $cutlass->pluginMatches($config))
    {
        $cutlass->pluginMismatched($root);

        continue;
    }

    $cutlass->pluginMatched($root);

    @require_once $root.'/plugin.php';

    $cutlass->loadPlugin($config);
}

/**
 * Boot Cutlass.
 */
$cutlass->boot();
