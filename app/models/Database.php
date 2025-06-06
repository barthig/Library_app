<?php

namespace App\Models;

use PDO;
use PDOException;

class Database
{
    private string $host;
    private string $port;
    private string $dbName;
    private string $user;
    private string $password;
    private ?PDO $pdo = null;


    private static ?PDO $instance = null;


     
    public function __construct()
    {
        $this->host     = getenv('DB_HOST') ?: (getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'db');
        $this->port     = getenv('DB_PORT') ?: (getenv('DB_PORT') !== false ? getenv('DB_PORT') : '5432');

     
        if (getenv('DB_NAME') !== false) {
            $this->dbName = getenv('DB_NAME');
        } elseif (getenv('POSTGRES_DB') !== false) {
            $this->dbName = getenv('POSTGRES_DB');
        } else {
            $this->dbName = 'library_db';
        }


        if (getenv('DB_USER') !== false) {
            $this->user = getenv('DB_USER');
        } elseif (getenv('POSTGRES_USER') !== false) {
            $this->user = getenv('POSTGRES_USER');
        } else {
            $this->user = 'postgres';
        }

        if (getenv('DB_PASSWORD') !== false) {
            $this->password = getenv('DB_PASSWORD');
        } elseif (getenv('POSTGRES_PASSWORD') !== false) {
            $this->password = getenv('POSTGRES_PASSWORD');
        } else {
            $this->password = 'password';
        }
    }

    /**
     * @return PDO
     * @throws PDOException
     */
    public function connect(): PDO
    {
        if ($this->pdo === null) {
    
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s;options=--client_encoding=UTF8',
                $this->host,
                $this->port,
                $this->dbName
            );

            try {
                $this->pdo = new PDO($dsn, $this->user, $this->password);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
              
                $this->pdo->exec("SET client_encoding TO 'UTF8'");
            } catch (PDOException $e) {
        
                throw new PDOException(
                    'Failed to connect to the database: ' . $e->getMessage(),
                    (int)$e->getCode(),
                    $e
                );
            }
        }

        return $this->pdo;
    }


    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $temp = new self();
            self::$instance = $temp->connect();
        }
        return self::$instance;
    }
}
