<?php


use MiddleWare\EnvironmentMiddleWare;
use MiddleWare\ProjectMiddleWare;

class App
{
    private $app;

    public function __construct()
    {
        $config = require __DIR__ . '/config/settings.php';
        $app = new \Slim\App($config);

        $routes = require __DIR__ . '/routes.php';
        $routes($app);

        $container = $app->getContainer();
        $app->add(new ProjectMiddleWare($container));
        $app->add(new EnvironmentMiddleWare($container));

        $dependencies = require __DIR__ . '/dependencies.php';
        $dependencies($app);
        $this->app = $app;
    }

    /**
     * Get an instance of the application.
     *
     * @return \Slim\App
     */
    public function get()
    {
        return $this->app;
    }
}