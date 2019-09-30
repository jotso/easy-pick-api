<?php

namespace Model;

use DateTime;
use Lib\Utils;
use Slim\Container;

class EasyPick
{
    public $pdoEasyPick;
    public $textTable = false;
    public $codesTable = false;
    public $usersTable = false;

    public function __construct(Container $container)
    {
        $baseTableName = $container->get('project_config')['db_base_table_name'];
        $this->textTable = $baseTableName . '_text';
        $this->codesTable = $baseTableName . '_codes';
        $this->usersTable = $baseTableName . '_users';

        $this->pdoEasyPick = new Pdo($container->get('db_easy_pick'));
    }

    public function isPreLaunch()
    {
        $onlinedate = $this->pdoEasyPick->getString('Start date', $this->textTable) . ' 00:00:00';
        return new DateTime() < new DateTime($onlinedate);
    }

    public function isOffline()
    {
        $offlinedate = $this->pdoEasyPick->getString('End date', $this->textTable) . ' 00:00:00';
        return new DateTime() > new DateTime($offlinedate);
    }

    public function getTextByDesctiption($description)
    {
        return $this->pdoEasyPick->getString($description, $this->textTable);
    }

    public function getCodeUsed($code)
    {
        $select = sprintf("SELECT used FROM %s WHERE code = ? LIMIT 1", $this->codesTable);
        return $this->pdoEasyPick->getValue($select, [$code]);
    }

    public function setCodeUsed($code, $used = 1)
    {
        $this->pdoEasyPick->query(sprintf("UPDATE %s SET used = ? WHERE code = ?", $this->codesTable), [$used, $code]);
    }

    public function insertUser(array $params)
    {
        return $this->pdoEasyPick->insert($this->usersTable, $params);
    }

    public function getUsersByEmail($email)
    {
        $select = sprintf("SELECT * FROM %s WHERE email = ?", $this->usersTable);
        return $this->pdoEasyPick->query($select, [$email]);
    }
}
