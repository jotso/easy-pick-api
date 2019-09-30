<?php

use Controller\AdminController;
use Controller\LoginController;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Controller\EasyPickController;

return function (App $app) {
    $container = $app->getContainer();

    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });

    $app->group('/api/{projectId}', function (App $app) use ($container) {
        $app->get('/init', function (Request $request, Response $response, array $args) use ($container) {
            $data = (new EasyPickController($container))->initAction();
            return $response->withJson($data);
        });

        $app->post('/checkcode', function (Request $request, Response $response, array $args) use ($container) {
            $data = (new EasyPickController($container))->checkCodeAction($request->getParam('code'));
            return $response->withJson($data);
        });

        $app->post('/checkfield', function (Request $request, Response $response, array $args) use ($container) {
            $data = (new EasyPickController($container))->checkFieldAction($request->getParams());
            return $response->withJson($data);
        });

        $app->post('/submitnaw', function (Request $request, Response $response, array $args) use ($container) {
            $data = (new EasyPickController($container))->subNawAction($request->getParams());
            return $response->withJson($data);
        });

        $app->post('/addtomailcamp', function (Request $request, Response $response, array $args) use ($container) {
            $data = (new EasyPickController($container))->addToMailCamp($request->getParams());
            return $response->withJson($data);
        });
    });

    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($req, $res) {
        $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
        return $handler($req, $res);
    });

};
