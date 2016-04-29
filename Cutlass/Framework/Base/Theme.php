<?php namespace Cutlass\Framework\Base;

use Cutlass\Framework\Theme as ThemeContract;
use Illuminate\Contracts\Container\Container;

class Theme implements ThemeContract {

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var array
     */
    protected $config = null;

    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * @param $path
     */
    public function __construct($path)
    {
        $this->setBasePath($path);
    }

    /**
     * Activate the theme.
     *
     * @return void
     */
    public function activate()
    {
        //
    }

    /**
     * Deactivate the theme.
     *
     * @return void
     */
    public function deactivate()
    {
        //
    }

    /**
     * Get the configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        if (is_null($this->config))
        {
            $this->config = file_exists("{$this->getBasePath()}/cutlass.config.php")
                ? require "{$this->getBasePath()}/cutlass.config.php"
                : [];
        }

        return $this->config;
    }

    /**
     * Set the base path.
     *
     * @param $path
     */
    public function setBasePath($path)
    {
        $this->path = $path;
    }

    /**
     * Get the base path.
     *
     * @return mixed
     */
    public function getBasePath()
    {
        return $this->path;
    }

    /**
     * Sets the IoC Container.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Gets the IoC Container.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

}
