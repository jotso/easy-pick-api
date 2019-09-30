<?php
require_once __DIR__ . '/../../../App.php';

use Controller\Dto;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use PHPUnit\Framework\TestCase;

class CheckCodeActionTest extends EasyPickTestCase
{
    protected static $environment;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$environment = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/api/0/checkcode',
            'HTTP_ORIGIN' => 'https://phpunit-easypick.nl',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);
    }

    /**
     * @dataProvider checkCodeProvider
     */
    public function testCheckCodeAction($code, $result)
    {
        $request = Request::createFromEnvironment(self::$environment)->withParsedBody(
            [
                'code' => $code,
            ]
        );
        $response = self::$app->process($request, new Response());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(json_encode($result), (string)$response->getBody());
    }

    public function checkCodeProvider()
    {
        return [
            ['', [
                'answer' => Dto::STATUS_ERROR,
                'messages' => [[
                    'field' => 'code',
                    'message' => 'Vul een geldige code in',
                ]]
            ]],
            ['12', [
                'answer' => Dto::STATUS_ERROR,
                'messages' => [[
                    'field' => 'code',
                    'message' => 'Deze code is niet compleet',
                ]]
            ]],
            ['phpunitnonexisting', [
                'answer' => Dto::STATUS_ERROR,
                'messages' => [[
                    'field' => 'code',
                    'message' => 'Deze code is ongeldig',
                ]]
            ]],
            ['phpunitused', [
                'answer' => Dto::STATUS_ERROR,
                'messages' => [[
                    'field' => 'code',
                    'message' => 'Deze code is reeds verzilverd',
                ]]
            ]],
            ['phpunit2019', [
                'answer' => Dto::STATUS_OK
            ]],
        ];
    }
}
