<?php


namespace Model;

use Lib\Utils;
use PDOException;

class Pdo
{
    private $_connection;

    public function __construct(\PDO $connection)
    {
        $this->_connection = $connection;
    }

    public function getString($string, $texttable)
    {
        return $this->getValue("SELECT text FROM $texttable WHERE description = ?", [$string]);
    }

    /**
     * @param $select
     * @param array $params
     * @param string $mode
     * @return array|int
     */
    public function query($select, $params = array(), $mode = "assoc")
    {
        if (count($params) > 0) {
            $qry = $this->_connection->prepare($select);
            $qry->execute($params);
        } else {
            $qry = $this->_connection->query($select);
        }
        if (strtoupper(substr($select, 0, 6)) == "SELECT") {
            switch ($mode) {
                case "assoc":
                    $res = $qry->fetchAll(\PDO::FETCH_ASSOC);
                    break;
                case "num":
                    $res = $qry->fetchAll(\PDO::FETCH_NUM);
                    break;
                case "both":
                    $res = $qry->fetchAll(\PDO::FETCH_BOTH);
                    break;
                case "keypair":
                    $res = $qry->fetchAll(\PDO::FETCH_KEY_PAIR);
                    break;
            }
            return $res;
        } else {
            return $qry->rowCount();
        }
    }

    /**
     * @param $select
     * @param array $params
     * @return string|null
     */
    public function getValue($select, $params = array())
    {
        if (count($params) > 0) {
            $qry = $this->_connection->prepare($select);
            $qry->execute($params);
        } else {
            $qry = $this->_connection->query($select);
        }
        $res = $qry->fetch(\PDO::FETCH_NUM);
        if ($qry->rowCount() > 0) {
            return trim($res[0]);
        } else {
            return NULL;
        }
    }

    /**
     * Insert using named parameters.
     *
     * @param $table
     * @param $params
     * @param bool $raw
     * @return string
     */
    public function insert($table, $params, $raw = false)
    {
        $fields = [];
        $values = [];
        foreach ($params as $k => $v) {
            $fields[] = $k;
            $v = trim($v);
            if (!$raw) {
                $v = htmlspecialchars($v);
            }
            $params[$k] = $v;
            $values[] = ":$k";
        }
        $fields = implode(', ', $fields);
        $values = implode(', ', $values);
        $query = "INSERT INTO $table ($fields) VALUES ($values)";
        $statement = $this->_connection->prepare($query);
        $statement->execute($params);
        return $this->_connection->lastInsertId();
    }

    /**
     * Update using named parameters.
     *
     * @param $table
     * @param $values
     * @param $id
     * @param bool $raw
     * @return int
     */
    public function update($table, $params, $id, $raw = false)
    {
        $values = [];
        foreach ($params as $k => $v) {
            $sets[] = $k . '=:' . $k;
            $v = trim($v);
            if (!$raw) {
                $v = htmlspecialchars($v);
            }
            $values[$k] = $v;
        }
        $values['id'] = intval($id);
        $sets = implode(', ', $sets);
        $s = "UPDATE $table SET $sets WHERE id=:id";
        $qry = $this->_connection->prepare($s);
        $qry->execute($values);
        return $qry->rowCount();
    }
}