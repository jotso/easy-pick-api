<?php

use \Slim\Http\Environment;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use MiddleWare\EnvironmentMiddleWare;
use PHPUnit\Framework\TestCase;

class EnvironmentMiddeWareTest extends TestCase
{
    public function testEnvMiddleWareMissingOrigin()
    {
        $container = new Container([]);
        $next = function ($req, $res) {
            return $res;
        };
        $environment = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/api/0/init',
        ]);
        $request = Request::createFromEnvironment($environment);
        $response = new Response();

        $envMiddleWare = new EnvironmentMiddleWare($container);
        $response = $envMiddleWare($request, $response, $next);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testEnvMiddleWareAllGood()
    {
        $container = new Container([]);
        $expected = 'https://staging.blank-template-2.activatiemarketing.nl';
        $next = function ($req, $res) {
            return $res;
        };
        $environment = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/api/0/init',
            'HTTP_ORIGIN' => $expected
        ]);
        $request = Request::createFromEnvironment($environment);
        $response = new Response();

        $envMiddleWare = new EnvironmentMiddleWare($container);
        $response = $envMiddleWare($request, $response, $next);

        $this->assertSame($expected, $container['origin']);
        $this->assertSame(EnvironmentMiddleWare::ENV_STAGING, $container['env']);
    }
}
