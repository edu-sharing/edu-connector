<?php

namespace connector\lib;

class Database extends \PDO
{
    private string $driver;
    public function __construct()
    {
        $dsn = DBTYPE . ":host=" . DBHOST . ";port=" . DBPORT;
        if (defined('DBNAME') && DBNAME !== '') {
            $dsn .= ";dbname=" . DBNAME;
        }
        parent::__construct($dsn, DBUSER, DBPASSWORD);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->driver = DBTYPE;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

}