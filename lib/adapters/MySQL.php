<?php

require_once dirname(__FILE__) . DS . 'Interface.php';

class DBV_Adapter_MySQL implements DBV_Adapter_Interface
{

    /**
     * @var PDO
     */
    protected $_connection;

    public function connect($host = false, $username = false, $password = false, $database_name = false)
    {
        try {
            $this->_connection = new PDO("mysql:host=$host;dbname=$database_name", $username, $password);
            $this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new DBV_Exception($e->getMessage(), $e->getCode());
        }
    }

    public function query($sql)
    {
        try {
            return $this->_connection->query($sql);
        } catch (PDOException $e) {
            throw new DBV_Exception($e->getMessage(), $e->getCode());
        }
    }

    public function getSchema()
    {
        return array_merge(
            $this->getTables(),
            $this->getViews(),
            $this->getTriggers(),
            $this->getProcedures(),
            $this->getFunctions()
        );
    }

    public function getTables($prefix = false)
    {
        $return = array();

        $result = $this->query('SHOW FULL TABLES');
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            if ($row[1] != 'BASE TABLE') {
                continue;
            }
            $return[] = ($prefix ? "{$prefix} " : '') . $row[0];
        }       

        return $return;
    }

    public function getViews($prefix = false)
    {
        $return = array();

        $result = $this->query('SHOW FULL TABLES');
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            if ($row[1] != 'VIEW') {
                continue;
            }
            $return[] = ($prefix ? "{$prefix} " : '') . $row[0];
        }       

        return $return;
    }   

    public function getTriggers($prefix = false)
    {
        $return = array();

        $result = $this->query('SHOW TRIGGERS');
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $return[] = ($prefix ? "{$prefix} " : '') . $row[0];
        }   

        return $return;
    }

    public function getFunctions($prefix = false)
    {
        $return = array();

        $result = $this->query('SHOW FUNCTION STATUS');
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $return[] = ($prefix ? "{$prefix} " : '') . $row[1];
        }   

        return $return;
    }   

    public function getProcedures($prefix = false)
    {
        $return = array();

        $result = $this->query('SHOW PROCEDURE STATUS');
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $return[] = ($prefix ? "{$prefix} " : '') . $row[1];
        }

        return $return;
    }

    public function getSchemaObject($name)
    {
        $index = 1;
        switch ($name) {
            case in_array($name, $this->getTables()):
                $query = "SHOW CREATE TABLE `$name`";
                break;
            case in_array($name, $this->getViews()):
                $query = "SHOW CREATE VIEW `$name`";
                break;
            case in_array($name, $this->getTriggers()):
                $query = "SHOW CREATE TRIGGER `$name`";
                $index = 2;
                break;
            case in_array($name, $this->getProcedures()):
                $query = "SHOW CREATE PROCEDURE `$name`";
                $index = 2;
                break;
            case in_array($name, $this->getFunctions()):
                $query = "SHOW CREATE FUNCTION `$name`";
                $index = 2;
                break;
            default:
                throw new DBV_Exception("<strong>$name</strong> not found in the database");
        }

        $result = $this->query($query);

        $row = $result->fetch(PDO::FETCH_NUM);
        return $row[$index];
    }

}
