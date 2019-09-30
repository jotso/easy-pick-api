<?php
require_once __DIR__ . '/../App.php';

use PHPUnit\Framework\TestCase;

class EasyPickTestCase extends TestCase
{
    protected static $app;
    protected static $codesTable = '00_phpunit_easy_pick_codes';
    protected static $usersTable = '00_phpunit_easy_pick_users';

    public static function setUpBeforeClass()
    {
        self::$app = (new App())->get();
        $pdo = new PDO('mysql:host=;dbname=;charset=UTF8', '', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $c = self::$app->getContainer();
        $c['db_easy_pick'] = $pdo;
    }

    public static function tearDownAfterClass()
    {
        $pdo = self::$app->getContainer()->get('db_easy_pick');
        $pdo->query(sprintf("UPDATE %s SET used=0 WHERE code='%s'", self::$codesTable, 'phpunit2019'));
        $pdo->query(sprintf("TRUNCATE %s", self::$usersTable));
    }

    protected function setCodeUnused()
    {
        $pdo = self::$app->getContainer()->get('db_easy_pick');
        $pdo->query(sprintf("UPDATE %s SET used=0 WHERE code='%s'", self::$codesTable, 'phpunit2019'));
    }
}