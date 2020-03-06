<?php

namespace LaraCrud\Helpers;

class Connector
{
    /**
     * PHP DATABSE OBJECT
     * @var \PDO
     */
    protected $pdo;

    public function __construct($dsn = '', $username = '', $password = '', $options = [])
    {
        $isLaravel = false;
        if (function_exists('app')) {
            $laravel = app();
            if (is_object($laravel)) {
                $db = app('db');
                $this->pdo = $db->connection()->getPdo();
                $isLaravel = true;
            }
        }
        if (!$isLaravel) {
            $this->pdo = new \PDO($dsn, $username, $password, $options);
        }
    }

    public function pdo()
    {
        if ($this->pdo instanceof \PDO) {
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
            return $this->pdo;
        }
        throw new \Exception(' PDO connection is not defined');
    }
}
