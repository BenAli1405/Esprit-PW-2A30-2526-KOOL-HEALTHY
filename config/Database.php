<?php

class Database
{
    private $host = '127.0.0.1';
    private $port = '3307';
    private $dbName = 'projetweb';
    private $username = 'root';
    private $password = '';
    private ?PDO $pdo = null;

    public function getConnection(): \PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $this->host, $this->port, $this->dbName);
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new \PDO($dsn, $this->username, $this->password, $options);
        }

        return $this->pdo;
    }
}
