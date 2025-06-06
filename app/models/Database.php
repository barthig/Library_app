<?php

namespace App\Models;

use PDO;
use PDOException;

class Database
{
    /*** fields for an instance connection ***/
    private string $host;
    private string $port;
    private string $dbName;
    private string $user;
    private string $password;
    private ?PDO $pdo = null;

    /*** field for the static singleton ***/
    private static ?PDO $instance = null;

    /**
     * Instance constructor.
     * Sets all properties based on environment variables:
     * - first tries DB_NAME / DB_USER / DB_PASSWORD (CampTrailApp),
     * - if missing, falls back to POSTGRES_DB / POSTGRES_USER / POSTGRES_PASSWORD (Library).
     */
    public function __construct()
    {
        $this->host     = getenv('DB_HOST') ?: (getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'db');
        $this->port     = getenv('DB_PORT') ?: (getenv('DB_PORT') !== false ? getenv('DB_PORT') : '5432');

        // first DB_NAME (CampTrailApp), otherwise POSTGRES_DB (Library) or a default value
        if (getenv('DB_NAME') !== false) {
            $this->dbName = getenv('DB_NAME');
        } elseif (getenv('POSTGRES_DB') !== false) {
            $this->dbName = getenv('POSTGRES_DB');
        } else {
            $this->dbName = 'library_db';
        }

        // first DB_USER, then POSTGRES_USER, otherwise 'postgres'
        if (getenv('DB_USER') !== false) {
            $this->user = getenv('DB_USER');
        } elseif (getenv('POSTGRES_USER') !== false) {
            $this->user = getenv('POSTGRES_USER');
        } else {
            $this->user = 'postgres';
        }

        // first DB_PASSWORD, then POSTGRES_PASSWORD, otherwise 'password'
        if (getenv('DB_PASSWORD') !== false) {
            $this->password = getenv('DB_PASSWORD');
        } elseif (getenv('POSTGRES_PASSWORD') !== false) {
            $this->password = getenv('POSTGRES_PASSWORD');
        } else {
            $this->password = 'password';
        }
    }

    /**
     * Instance method connect().
     * If a connection hasn't been opened ($pdo === null), create a new PDO
     * with the DSN and appropriate attributes.
     *
     * @return PDO
     * @throws PDOException
     */
    public function connect(): PDO
    {
        if ($this->pdo === null) {
            // DSN compatible with CampTrailApp, with UTF-8 encoding
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
                // additionally, if the DSN with options didn't work, enforce encoding:
                $this->pdo->exec("SET client_encoding TO 'UTF8'");
            } catch (PDOException $e) {
                // as a best practice: don't reveal the password or DSN details in the message
                throw new PDOException(
                    'Failed to connect to the database: ' . $e->getMessage(),
                    (int)$e->getCode(),
                    $e
                );
            }
        }

        return $this->pdo;
    }

    /**
     * Old static getConnection() method that still works
     * (uses POSTGRES_* environment variables or defaults).
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // we can create a temporary instance and return its PDO
            $temp = new self();
            self::$instance = $temp->connect();
        }
        return self::$instance;
    }
}
