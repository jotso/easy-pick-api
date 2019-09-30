<?php
require_once __DIR__ . '/../../../App.php';

use Controller\Dto;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use PHPUnit\Framework\TestCase;

class CheckFieldActionTest extends EasyPickTestCase
{
    protected static $environment;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$environment = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/api/0/checkfield',
            'HTTP_ORIGIN' => 'https://phpunit-easypick.nl',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);
    }

    public function testCheckFieldActionFails()
    {
        $request = Request::createFromEnvironment(self::$environment)->withParsedBody(
            [
                'field' => 'naam',
                'naam' => 'Uni'
            ]
        );
        $response = self::$app->process($request, new Response());

        $this->assertSame(200, $response->getStatusCode());
        $expected = [
            'answer' => Dto::STATUS_ERROR,
            'messages' => [[
                'field' => 'naam',
                'message' => 'Vul je volledige naam in',
            ]]
        ];
        $this->assertSame(json_encode($expected), (string)$response->getBody());
    }

    public function testCheckFieldActionWithSuccess()
    {
        $request = Request::createFromEnvironment(self::$environment)->withParsedBody(
            [
                'field' => 'naam',
                'naam' => 'Unit Test'
            ]
        );
        $response = self::$app->process($request, new Response());

        $this->assertSame(200, $response->getStatusCode());
        $expected = [
            'answer' => Dto::STATUS_OK
        ];
        $this->assertSame(json_encode($expected), (string)$response->getBody());
    }
}
