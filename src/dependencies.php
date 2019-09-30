<?php

use Slim\App;
use Slim\Views\PhpRenderer;
use Lib\Mail;
use Model\EasyPick;

return function (App $app) {
    $container = $app->getContainer();

    $container['db_easy_pick'] = function ($c) {
        $db = $c['settings']['db_easy_pick'];

        $dbHost = $db['host'];
        $dbName = $db['dbname'];

        $pdo = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName . ';charset=UTF8', $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    };

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new PhpRenderer($settings['template_path']);
    };

    $container['view'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        $view = new \Slim\Views\Twig($settings['template_path'], []);

        $router = $c->get('router');
        $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
        $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

        return $view;
    };

    $container['mail'] = function ($c) {
        return new Mail($c);
    };

    $container['model_easy_pick'] = function ($c) {
        return new EasyPick($c);
    };
};
