<?php

namespace Core\Database;

class Dsn {
    private string $host;
    private string $user;
    private string $password;
    private string $dbname;
    private int $port;
    private string $dsn;

    public function __construct() {
        $driver = getenv('POSTGRES_DRIVER') ?: 'pgsql';
        if ($driver !== 'pgsql') {
            throw new \RuntimeException("SGBD non supporté : seul PostgreSQL est autorisé.");
        }

        $this->host     = getenv('POSTGRES_HOST') ?: 'localhost';
        $this->user     = getenv('POSTGRES_USER') ?: '';
        $this->password = getenv('POSTGRES_PASSWORD') ?: '';
        $this->dbname   = getenv('POSTGRES_DB') ?: '';
        $this->port     = (int)(getenv('POSTGRES_PORT') ?: 5432);
        $this->dsn      = 'pgsql:';
    }

    public function getUser(): string {
        return $this->user;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function addHostToDsn(): self {
        $this->dsn .= "host=$this->host;";
        return $this;
    }

    public function addDbnameToDsn(): self {
        $this->dsn .= "dbname=$this->dbname;";
        return $this;
    }

    public function addPortToDsn(): self {
        $this->dsn .= "port=$this->port;";
        return $this;
    }

    public function getDsn(): string {
        return $this->dsn;
    }

    public function getDbName(): string {
        return $this->dbname;
    }
}
