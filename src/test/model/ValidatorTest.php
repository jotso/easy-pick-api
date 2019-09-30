<?php

use Slim\Container;
use Model\Validator;
use Model\EasyPick;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends EasyPickTestCase
{
    protected $container;

    public function setUp()
    {
        $this->container = new Container([]);
    }

    public function testPlayByEmailTooManyTries()
    {
        $this->container['project_config'] = [
            'play_by_email' => true,
        ];
        $users = ['joost', 'kelso'];
        $easyPickMock = $this->createMock(EasyPick::class);
        $easyPickMock->expects($this->exactly(1))
            ->method('getUsersByEmail')
            ->willReturn($users);
        $this->container['model_easy_pick'] = $easyPickMock;

        $validator = new Validator($this->container);
        $result = $validator->playByEmail('test@test.com');

        $this->assertFalse($result);
        $this->assertSame('Je hebt al 1 keer meegedaan', $validator->getMessages()['messages'][0]['message']);
    }

    public function testPlayByEmailNoUsers()
    {
        $this->container['project_config'] = [
            'play_by_email' => true,
        ];
        $easyPickMock = $this->createMock(EasyPick::class);
        $easyPickMock->expects($this->exactly(1))
            ->method('getUsersByEmail')
            ->willReturn(null);
        $this->container['model_easy_pick'] = $easyPickMock;

        $validator = new Validator($this->container);
        $result = $validator->playByEmail('test@test.com');

        $this->assertTrue($result);
    }

    public function testPlayByEmailNotSet()
    {
        $easyPickMock = $this->createMock(EasyPick::class);
        $this->container['model_easy_pick'] = $easyPickMock;
        $this->container['project_config'] = [];
        $validator = new Validator($this->container);
        $result = $validator->playByEmail('test@test.com');

        $this->assertTrue($result);
    }

    public function testPlayByEmailMaxTries()
    {
        $this->container['project_config'] = [
            'play_by_email' => true,
            'play_by_email_max_tries' => 3
        ];
        $users = ['joost', 'kelso', 'eric'];
        $easyPickMock = $this->createMock(EasyPick::class);
        $easyPickMock->expects($this->exactly(1))
            ->method('getUsersByEmail')
            ->willReturn($users);
        $this->container['model_easy_pick'] = $easyPickMock;

        $validator = new Validator($this->container);
        $result = $validator->playByEmail('test@test.com');

        $this->assertFalse($result);
        $this->assertSame('Je hebt al 3 keer meegedaan', $validator->getMessages()['messages'][0]['message']);
    }
}