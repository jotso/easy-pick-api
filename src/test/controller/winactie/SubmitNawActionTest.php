<?php

use Controller\Dto;
use Lib\Mail;
use Lib\Utils;
use Model\EasyPick;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class SubmitNawActionTest extends EasyPickTestCase
{
    protected static $userData = [
        'code' => 'phpunit2019',
        'naam' => 'Unit Test',
        'email' => 'phpunit@activatiemarketing.nl',
        'emailrep' => 'phpunit@activatiemarketing.nl',
        'avw' => '1',
        'optin' => '1',
        'optin2' => '1'
    ];

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    /**
     * Reset the database after each test.
     */
    public function tearDown()
    {
        $pdo = self::$app->getContainer()->get('db_easy_pick');
        $pdo->query(sprintf("UPDATE %s SET used=0 WHERE code='%s'", self::$codesTable, 'phpunit2019'));
        $pdo->query(sprintf("TRUNCATE %s", self::$usersTable));
    }

    public function testPlaybyEmailWithSuccessAction()
    {
        $mailMock = $this->createMock(Mail::class);
        $mailMock->expects($this->exactly(1))
            ->method('fetchMailTemplateContent')
            ->willReturn('lorem ipsum');

        $mailMock->expects($this->exactly(1))
            ->method('send')
            ->willReturn(true);

        $c = self::$app->getContainer();
        $c['mail'] = $mailMock;

        $userData = self::$userData;
        $response = $this->submit(2);
        $this->assertSame(200, $response->getStatusCode());

        $acutalUsers = (new EasyPick(self::$app->getContainer()))->getUsersByEmail("phpunit@activatiemarketing.nl");
        $this->assertCount(1, $acutalUsers);
        $acutalUsers = self::filterUser($acutalUsers);
        unset($userData['avw']);
        unset($userData['emailrep']);
        $this->assertSame($userData, $acutalUsers[0]);
        $acutalCode = (new EasyPick(self::$app->getContainer()))->getCodeUsed("phpunit2019");
        $this->assertSame(0, (int)$acutalCode);
    }

    public function testPlaybyEmail2MaxTriesWithSuccessAction()
    {
        $mailMock = $this->createMock(Mail::class);
        $mailMock->expects($this->exactly(2))
            ->method('fetchMailTemplateContent')
            ->willReturn('lorem ipsum');

        $mailMock->expects($this->exactly(2))
            ->method('send')
            ->willReturn(true);

        $c = self::$app->getContainer();
        $c['mail'] = $mailMock;

        $userData = self::$userData;
        $response = $this->submit(2);
        $response = $this->submit(2);
        $this->assertSame(200, $response->getStatusCode());

        $acutalUsers = (new EasyPick(self::$app->getContainer()))->getUsersByEmail("phpunit@activatiemarketing.nl");
        $this->assertCount(2, $acutalUsers);
        $acutalCode = (new EasyPick(self::$app->getContainer()))->getCodeUsed("phpunit2019");
        $this->assertSame(0, (int)$acutalCode);
    }

    public function testPlaybyEmailMaxTriesFailsAction()
    {
        $mailMock = $this->createMock(Mail::class);
        $mailMock->expects($this->exactly(1))
            ->method('fetchMailTemplateContent')
            ->willReturn('lorem ipsum');

        $mailMock->expects($this->exactly(1))
            ->method('send')
            ->willReturn(true);

        $c = self::$app->getContainer();
        $c['mail'] = $mailMock;

        $userData = self::$userData;
        $response = $this->submit(3);
        $response = $this->submit(3);
        $expected = [
            'answer' => Dto::STATUS_ERROR,
            'messages' => [[
                'field' => 'email',
                'message' => 'Je hebt al 1 keer meegedaan',
            ]]
        ];
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(json_encode($expected), (string)$response->getBody());

        $acutalUsers = (new EasyPick(self::$app->getContainer()))->getUsersByEmail("phpunit@activatiemarketing.nl");
        $this->assertCount(1, $acutalUsers);
        $acutalCode = (new EasyPick(self::$app->getContainer()))->getCodeUsed("phpunit2019");
        $this->assertSame(0, (int)$acutalCode);
    }

    public function testPlaybyUniqueCodeWithSuccessAction()
    {
        $mailMock = $this->createMock(Mail::class);
        $mailMock->expects($this->exactly(1))
            ->method('fetchMailTemplateContent')
            ->willReturn('lorem ipsum');

        $mailMock->expects($this->exactly(1))
            ->method('send')
            ->willReturn(true);

        $c = self::$app->getContainer();
        $c['mail'] = $mailMock;

        $userData = self::$userData;
        $response = $this->submit(0);
        $this->assertSame(200, $response->getStatusCode());

        $acutalUsers = (new EasyPick(self::$app->getContainer()))->getUsersByEmail("phpunit@activatiemarketing.nl");
        $this->assertCount(1, $acutalUsers);
        $acutalUsers = self::filterUser($acutalUsers);
        unset($userData['avw']);
        unset($userData['emailrep']);
        $this->assertSame($userData, $acutalUsers[0]);
        $acutalCode = (new EasyPick(self::$app->getContainer()))->getCodeUsed("phpunit2019");
        $this->assertSame(1, (int)$acutalCode);
    }

    public function testPlaybyUniqueCodeFailsAction()
    {
        $mailMock = $this->createMock(Mail::class);
        $mailMock->expects($this->exactly(1))
            ->method('fetchMailTemplateContent')
            ->willReturn('lorem ipsum');

        $mailMock->expects($this->exactly(1))
            ->method('send')
            ->willReturn(true);

        $c = self::$app->getContainer();
        $c['mail'] = $mailMock;

        $userData = self::$userData;
        $response = $this->submit(0);
        $response = $this->submit(0);
        $expected = [
            'answer' => Dto::STATUS_ERROR,
            'messages' => [[
                'field' => 'code',
                'message' => 'Deze code is reeds verzilverd',
            ]]
        ];
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(json_encode($expected), (string)$response->getBody());

        $acutalUsers = (new EasyPick(self::$app->getContainer()))->getUsersByEmail("phpunit@activatiemarketing.nl");
        $this->assertCount(1, $acutalUsers);
    }

    private function submit($projectId)
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => "/api/$projectId/submitnaw",
            'HTTP_ORIGIN' => 'https://phpunit-easypick.nl',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);
        $request = Request::createFromEnvironment($env)->withParsedBody(self::$userData);
        $response = self::$app->process($request, new Response());
        return $response;
    }

    private static function filterUser($user)
    {
        unset($user[0]['id']);
        unset($user[0]['datum']);
        unset($user[0]['tijd']);
        unset($user[0]['serial']);
        return $user;
    }
}
