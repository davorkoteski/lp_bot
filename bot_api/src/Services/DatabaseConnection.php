<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/5/2019
 * Time: 1:25 PM
 */

namespace App\Services;


class DatabaseConnection
{
    protected $connection;

    public function __construct() {
        $mysql = [
            'host' => 'localhost',
            'dbname' => 'lp_message',
            'username' => 'root',
            'password' => '',
            'options' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]
        ];
        $dns = sprintf('mysql:host=%s;dbname=%s', $mysql['host'], $mysql['dbname']);
        $connection = new \PDO($dns, $mysql['username'], $mysql['password'], $mysql['options']);
        $this->setConnection($connection);
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function setConnection($pValue)
    {
        $this->connection = $pValue;
        return $this;
    }

}