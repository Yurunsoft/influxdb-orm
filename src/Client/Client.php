<?php
namespace Yurun\InfluxDB\ORM\Client;

use Yurun\InfluxDB\ORM\Client\Database;

class Client extends \InfluxDB\Client
{
    /**
     * Query influxDB
     *
     * @param  string $database
     * @param  string $query
     * @param  array  $parameters
     *
     * @return \Yurun\InfluxDB\ORM\Client\ResultSet
     * @throws \InfluxDB\Client\Exception
     */
    public function query($database, $query, $parameters = [])
    {
        return parent::query($database, $query, $parameters);
    }

    /**
     * Use the given database
     *
     * @param  string $name
     * @return Database
     * @throws \InvalidArgumentException
     */
    public function selectDB($name)
    {
        return new Database($name, $this);
    }

}
