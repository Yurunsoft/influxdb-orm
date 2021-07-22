<?php

namespace Yurun\InfluxDB\ORM\Test\Tests;

use PHPUnit\Framework\TestCase;
use Yurun\InfluxDB\ORM\InfluxDBManager;
use Yurun\InfluxDB\ORM\Query\QueryBuilder;

class ResultSetTest extends TestCase
{
    public function testInitConnection(): void
    {
        $host = getenv('INFLUXDB_HOST') ?: '127.0.0.1';
        $port = getenv('INFLUXDB_PORT') ?: 8086;
        $username = getenv('INFLUXDB_USERNAME') ?: '';
        $password = getenv('INFLUXDB_PASSWORD') ?: '';
        $ssl = false;
        $verifySSL = false;
        $timeout = 0;
        $connectTimeout = 0;
        $defaultDatabase = getenv('INFLUXDB_TEST_DB') ?: 'db_influxdb_orm_dbtest';
        $path = '/';

        InfluxDBManager::setClientConfig('dbtest', $host, $port, $username, $password, $ssl, $verifySSL, $timeout, $connectTimeout, $defaultDatabase);

        $this->assertEquals(compact('host', 'port', 'username', 'password', 'ssl', 'verifySSL', 'timeout', 'connectTimeout', 'defaultDatabase', 'path'), InfluxDBManager::getClientConfig('dbtest'));
    }

    public function testGetRow(): void
    {
        $query = new QueryBuilder('dbtest');
        $result = $query->timezone(date_default_timezone_get())
                        ->from('a')
                        ->order('time')
                        ->select();
        $this->assertEquals([
            'id'    => '1',
            'value' => 1,
            'name'  => 'a',
            'time'  => '2019-01-01T01:01:01Z',
            'age'   => 11,
        ], $result->getRow());
        $this->assertEquals([
            'id'    => '2',
            'value' => 2,
            'name'  => 'b',
            'time'  => '2019-02-02T02:02:02Z',
            'age'   => 22,
        ], $result->getRow(1));
    }

    public function testGetScalar(): void
    {
        $query = new QueryBuilder('dbtest');
        $result = $query->timezone(date_default_timezone_get())
                        ->from('a')
                        ->field('count(*)')
                        ->select();
        $this->assertEquals(3, $result->getScalar());

        $result = $query->timezone(date_default_timezone_get())
                        ->from('b')
                        ->field('count(*)')
                        ->select();
        $this->assertEquals(19260817, $result->getScalar(1, 0, 19260817));
    }
}
