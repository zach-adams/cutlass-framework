<?php

if ( ! function_exists('dd'))
{
    /**
     * Dies and dumps.
     *
     * @return string
     */
    function dd()
    {
        call_user_func_array('dump', func_get_args());

        die;
    }
}

if ( ! function_exists('themes_directory'))
{
    /**
     * Gets the themes directory.
     *
     * @return string
     */
    function themes_directory()
    {
        return WP_CONTENT_DIR . '/themes';
    }
}

if ( ! function_exists('content_directory'))
{
    /**
     * Gets the content directory.
     *
     * @return string
     */
    function content_directory()
    {
        return WP_CONTENT_DIR;
    }
}

if ( ! function_exists('plugin_directory'))
{
    /**
     * Gets the plugin directory.
     *
     * @return string
     */
    function plugin_directory()
    {
        return WP_PLUGIN_DIR;
    }
}

if ( ! function_exists('response'))
{
    /**
     * Generates a response.
     *
     * @param  string  $body
     * @param  integer $status
     * @param  array   $headers
     * @return \Cutlass\Framework\Response
     */
    function response($body, $status = 200, $headers = null)
    {
        return new Cutlass\Framework\Response($body, $status, $headers);
    }
}

if ( ! function_exists('json_response'))
{
    /**
     * Generates a json response.
     *
     * @param  mixed   $jsonable
     * @param  integer $status
     * @param  array   $headers
     * @return \Cutlass\Framework\Response
     */
    function json_response($jsonable, $status = 200, $headers = null)
    {
        return new Cutlass\Framework\JsonResponse($jsonable, $status, $headers);
    }
}

if ( ! function_exists('redirect_response'))
{
    /**
     * Generates a redirect response.
     *
     * @param  string  $url
     * @param  integer $status
     * @param  array   $headers
     * @return \Cutlass\Framework\Response
     */
    function redirect_response($url, $status = 302, $headers = null)
    {
        return new Cutlass\Framework\RedirectResponse($url, $status, $headers);
    }
}

if ( ! function_exists('cutlass'))
{
    /**
     * Gets the cutlass container.
     *
     * @param  string $binding
     * @return string
     */
    function cutlass($binding = null)
    {
        $instance = Cutlass\Framework\Application::getInstance();

        if ( ! $binding)
        {
            return $instance;
        }

        return $instance[$binding];
    }
}

if ( ! function_exists('errors'))
{
    /**
     * Get the errors.
     *
     * @param string key
     * @return array
     */
    function errors($key = null)
    {
        $errors = cutlass('errors');
        $errors = isset($errors[0]) ? $errors[0] : $errors;

        if (!$key)
        {
            return $errors;
        }

        return array_get($errors, $key);
    }
}

if ( ! function_exists('session'))
{
    /**
     * Gets the session or a key from the session.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return \Illuminate\Session\Store|mixed
     */
    function session($key = null, $default = null)
    {
        if ($key === null)
        {
            return cutlass('session');
        }

        return cutlass('session')->get($key, $default);
    }
}

if ( ! function_exists('session_flashed'))
{
    /**
     * Gets the session flashbag or a key from the session flashbag.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return \Illuminate\Session\Store|mixed
     */
    function session_flashed($key = null, $default = [])
    {
        if ($key === null)
        {
            return cutlass('session')->getFlashBag();
        }

        return cutlass('session')->getFlashBag()->get($key, $default);
    }
}

if ( ! function_exists('view'))
{
    /**
     * Renders a twig view.
     *
     * @param  string $name
     * @param  array  $context
     * @return string
     */
    function view($name, $context = [])
    {
        dd($name);
        return response(cutlass('view')->render($name, $context));
    }
}

if ( ! function_exists('panel_url'))
{
    /**
     * Gets the url to a panel.
     *
     * @param  string $name
     * @param  array  $query
     * @return string
     */
    function panel_url($name, $query = [])
    {
        return add_query_arg($query, cutlass('panel')->url($name));
    }
}

if ( ! function_exists('route_url'))
{
    /**
     * Gets the url to a route.
     *
     * @param  string $name
     * @param  array  $args
     * @param  array  $query
     * @return string
     */
    function route_url($name, $args = [], $query = [])
    {
        return add_query_arg($query, cutlass('router')->url($name, $args));
    }
}

if ( ! function_exists('wp_register_theme_activation_hook')) {
    /**
     * Registers a theme activation hook
     *
     * @param string   $code     : Code of the theme. This can be the base folder of your theme. Eg if your theme is in folder 'mytheme' then code will be 'mytheme'
     * @param callback $function : Function to call when theme gets activated.
     */
    function wp_register_theme_activation_hook($code, $function)
    {
        $optionKey = "theme_is_activated_" . $code;
        if ( ! get_option($optionKey)) {
            call_user_func($function);
            update_option($optionKey, 1);
        }
    }
}

if ( ! function_exists('wp_register_theme_deactivation_hook')) {
    /**
     * Registers deactivation hook
     * 
     * @param string $code : Code of the theme. This must match the value you provided in wp_register_theme_activation_hook function as $code
     * @param callback $function : Function to call when theme gets deactivated.
     */
    function wp_register_theme_deactivation_hook($code, $function) {
        // store function in code specific global
        $GLOBALS["wp_register_theme_deactivation_hook_function" . $code]=$function;

        // create a runtime function which will delete the option set while activation of this theme and will call deactivation function provided in $function
        $fn=create_function('$theme', ' call_user_func($GLOBALS["wp_register_theme_deactivation_hook_function' . $code . '"]); delete_option("theme_is_activated_' . $code. '");');

        // add above created function to switch_theme action hook. This hook gets called when admin changes the theme.
        // Due to wordpress core implementation this hook can only be received by currently active theme (which is going to be deactivated as admin has chosen another one.
        // Your theme can perceive this hook as a deactivation hook.
        add_action("switch_theme", $fn);
    }
}

