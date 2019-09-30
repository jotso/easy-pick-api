<?php

use Lib\Utils;
use \Slim\Http\Environment;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use MiddleWare\ProjectMiddleWare;
use PHPUnit\Framework\TestCase;
use Slim\Route;

class ProjectMiddeWareTest extends TestCase
{

    private $container;
    private $next;
    private $request;

    public function setUp()
    {
        $this->container = new Container([]);
        $this->next = function ($req, $res) {
            return $res;
        };
        $environment = Environment::mock([]);
        $this->request = Request::createFromEnvironment($environment);
    }

    public function testMiddleWareOptionsPasses()
    {
        $route = new Route('OPTIONS', '/api/0/init', function () {});
        $request = $this->request->withAttributes(['route' => $route]);
        $projectMiddleWare = new ProjectMiddleWare($this->container);
        $response = $projectMiddleWare($request, new Response(), $this->next);

        $this->assertSame(['*'], $response->getHeader('Access-Control-Allow-Origin'));
    }

    public function testMiddleWareToegangskaartenPasses()
    {
        $environment = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/toegangskaarten/id123',
        ]);
        $request = Request::createFromEnvironment($environment);
        $route = new Route('GET', '/toegangskaarten/id123', function () {});
        $request = $request->withAttributes(['route' => $route]);
        $projectMiddleWare = new ProjectMiddleWare($this->container);
        $response = $projectMiddleWare($request, new Response(), $this->next);

        $this->assertSame(['*'], $response->getHeader('Access-Control-Allow-Origin'));
    }

    public function testMiddleWareMissingProjectId()
    {
        $route = new Route('GET', '/api/init', function () {});
        $request = $this->request->withAttributes(['route' => $route]);
        $projectMiddleWare = new ProjectMiddleWare($this->container);
        $response = $projectMiddleWare($request, new Response(), $this->next);

        $responseArr = json_decode((string)$response->getBody());
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Unable to handle request: Missing projectId', $responseArr->messages[0]);
    }

    public function testMiddleWareProjectConfigurationNotFound()
    {
        $route = new Route('GET', '/api/666/init', function () {});
        $route->setArgument('projectId', 666);
        $request = $this->request->withAttributes(['route' => $route]);
        $projectMiddleWare = new ProjectMiddleWare($this->container);
        $response = $projectMiddleWare($request, new Response(), $this->next);

        $responseArr = json_decode((string)$response->getBody());
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Unable to handle request: Project configuration with projectId 666 not found', $responseArr->messages[0]);
    }

    public function testMiddleWareDomainNotConfigured()
    {
        $route = new Route('GET', '/api/1/init', function () {});
        $route->setArgument('projectId', 1);
        $request = $this->request->withAttributes(['route' => $route]);
        $projectMiddleWare = new ProjectMiddleWare($this->container);
        $response = $projectMiddleWare($request, new Response(), $this->next);

        $responseArr = json_decode((string)$response->getBody());
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Unable to handle request: Project configuration error', $responseArr->messages[0]);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function testMiddleWareStripPrefix($code, $domain)
    {
        $container = new Container([
            'origin' => $domain
        ]);
        $route = new Route('GET', '/api/0/init', function () {});
        $route->setArgument('projectId', 0);
        $request = $this->request->withAttributes(['route' => $route]);
        $projectMiddleWare = new ProjectMiddleWare($container);
        $response = $projectMiddleWare($request, new Response(), $this->next);

        $this->assertSame($code, $response->getStatusCode());
    }

    public function prefixProvider()
    {
        return [
            [
                200, 'http://www.phpunit-easypick.nl'
            ],
            [
                200, 'http://staging.phpunit-easypick.nl'
            ],
            [
                200, 'http://www.staging.phpunit-easypick.nl'
            ],
            [
                200, 'http://test.phpunit-easypick.nl'
            ],
            [
                200, 'http://www.test.phpunit-easypick.nl'
            ],
            [
                200, 'http://phpunit-easypick.nl'
            ],
            [
                400, 'http://live.phpunit-easypick.nl'
            ]
        ];
    }
}
