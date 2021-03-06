<?php namespace Cutlass\Framework;

use Cutlass\Framework\Application;

class Widget {

    /**
     * @var \Cutlass\Framework\Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $widgets = [];

    /**
     * @param \Cutlass\Framework\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app;

        add_action('widgets_init', [$this, 'boot']);
    }

    /**
     * Boot the widgets.
     *
     * @return void
     */
    public function boot()
    {
        global $wp_widget_factory;

        foreach ($this->widgets as $widget)
        {
            register_widget($widget['class']);

            if (method_exists($instance = $wp_widget_factory->widgets[$widget['class']], 'boot'))
            {
                $this->app->call(
                    [$instance, 'boot'],
                    ['app' => $this->app, 'theme' => $widget['theme']]
                );
            }
        }
    }

    /**
     * Adds a widget.
     *
     * @param  string $widget
     * @param  Theme $theme
     * @return void
     */
    public function add($widget, Theme $theme = null)
    {
        $this->widgets[] = [
            'class'  => $widget,
            'theme' => $theme
        ];
    }

}
