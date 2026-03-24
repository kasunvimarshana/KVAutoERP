<?php

namespace Modules\Tenant\Domain\ValueObjects;

class DatabaseConfig
{
    private string $driver;
    private string $host;
    private int $port;
    private string $database;
    private string $username;
    private string $password;

    public function __construct(array $data)
    {
        $this->driver = $data['driver'] ?? 'mysql';
        $this->host = $data['host'] ?? '127.0.0.1';
        $this->port = (int)($data['port'] ?? 3306);
        $this->database = $data['database'] ?? '';
        $this->username = $data['username'] ?? '';
        $this->password = $data['password'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'driver'   => $this->driver,
            'host'     => $this->host,
            'port'     => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
}
