<?php
namespace Yurun\InfluxDB\ORM\Client;

use InfluxDB\Database\RetentionPolicy;

class Database extends \InfluxDB\Database
{
    /**
     * Query influxDB
     *
     * @param  string $query
     * @param  array  $params
     * @return \Yurun\InfluxDB\ORM\Client\ResultSet
     * @throws \InfluxDB\Client\Exception
     */
    public function query($query, $params = [])
    {
        return parent::query($query, $params);
    }

    /**
     * Create this database
     *
     * @param  RetentionPolicy $retentionPolicy
     * @param  bool            $createIfNotExists Deprecated parameter - to be removed
     * @return \Yurun\InfluxDB\ORM\Client\ResultSet
     * @throws InfluxDB\Database\Exception
     */
    public function create(RetentionPolicy $retentionPolicy = null, $createIfNotExists = false)
    {
        return parent::create($retentionPolicy, $createIfNotExists);
    }

    /**
     * @param  \InfluxDB\Database\RetentionPolicy $retentionPolicy
     * @return \Yurun\InfluxDB\ORM\Client\ResultSet
     */
    public function createRetentionPolicy(RetentionPolicy $retentionPolicy)
    {
        return parent::createRetentionPolicy($retentionPolicy);
    }

}
