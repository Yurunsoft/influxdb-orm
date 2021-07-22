<?php

namespace Yurun\InfluxDB\ORM\Client;

class Client extends \InfluxDB\Client
{
    /**
     * @var string
     */
    protected $path;

    public function __construct(
        string $host,
        int $port = 8086,
        string $username = '',
        string $password = '',
        bool $ssl = false,
        bool $verifySSL = false,
        float $timeout = 0,
        float $connectTimeout = 0,
        string $path = '/'
    ) {
        parent::__construct($host, $port, $username, $password, $ssl, $verifySSL, $timeout, $connectTimeout);
        $this->path = $path;
        $this->baseURI .= $path;
    }

    /**
     * Query influxDB.
     *
     * @param string $database
     * @param string $query
     * @param array  $parameters
     *
     * @return \Yurun\InfluxDB\ORM\Client\ResultSet
     *
     * @throws \InfluxDB\Client\Exception
     */
    public function query($database, $query, $parameters = [])
    {
        // @phpstan-ignore-next-line
        return parent::query($database, $query, $parameters);
    }

    /**
     * Use the given database.
     *
     * @param string $name
     *
     * @return Database
     *
     * @throws \InvalidArgumentException
     */
    public function selectDB($name)
    {
        return new Database($name, $this);
    }
}
