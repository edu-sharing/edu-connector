<?php

namespace connector\lib;

class Database extends \PDO
{
    private string $driver;
    public function __construct()
    {
        parent::__construct(DBTYPE . ":host=" . DBHOST . ";port=" . DBPORT, DBUSER, DBPASSWORD);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->driver = DBTYPE;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

}