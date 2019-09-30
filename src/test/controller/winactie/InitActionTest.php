<?php
require_once __DIR__ . '/../../../App.php';

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use PHPUnit\Framework\TestCase;
use Controller\Dto;

class InitActionTest extends EasyPickTestCase
{
    protected static $environment;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$environment = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/api/0/init',
            'HTTP_ORIGIN' => 'https://phpunit-easypick.nl'
        ]);
    }

    public function testInitActionWithSuccess()
    {
        $request = Request::createFromEnvironment(self::$environment);
        $response = self::$app->process($request, new Response());

        $this->assertSame(200, $response->getStatusCode());
        $result = json_decode((string)$response->getBody());
        $this->assertSame(['answer' => Dto::STATUS_OK], (array)$result);
    }
}
