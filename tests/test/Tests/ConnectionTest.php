<?php

namespace Yurun\InfluxDB\ORM\Test\Tests;

use PHPUnit\Framework\TestCase;
use Yurun\InfluxDB\ORM\InfluxDBManager;

class ConnectionTest extends TestCase
{
    public function testClientConfig(): void
    {
        $this->assertNull(InfluxDBManager::getClientConfig());

        $host = getenv('INFLUXDB_HOST') ?: '127.0.0.1';
        $port = getenv('INFLUXDB_PORT') ?: 8086;
        $username = getenv('INFLUXDB_USERNAME') ?: '';
        $password = getenv('INFLUXDB_PASSWORD') ?: '';
        $ssl = false;
        $verifySSL = false;
        $timeout = 0;
        $connectTimeout = 0;
        $defaultDatabase = getenv('INFLUXDB_TEST_ORM_DB') ?: 'db_influxdb_orm_ormtest';
        $path = '/';

        InfluxDBManager::setClientConfig('test', $host, $port, $username, $password, $ssl, $verifySSL, $timeout, $connectTimeout, $defaultDatabase);
        InfluxDBManager::setClientConfig('test2', $host, $port, $username, $password, $ssl, $verifySSL, $timeout, $connectTimeout, $defaultDatabase);

        $this->assertEquals(compact('host', 'port', 'username', 'password', 'ssl', 'verifySSL', 'timeout', 'connectTimeout', 'defaultDatabase', 'path'), InfluxDBManager::getClientConfig('test'));
    }

    public function testRemoveClientConfig(): void
    {
        InfluxDBManager::setClientConfig('testx', '127.0.0.1');
        $this->assertNotNull(InfluxDBManager::getClientConfig('testx'));
        InfluxDBManager::removeClientConfig('testx');
        $this->assertNull(InfluxDBManager::getClientConfig('testx'));
    }

    public function testDefaultClientName(): void
    {
        $this->assertNull(InfluxDBManager::getDefaultClientName());
        InfluxDBManager::setDefaultClientName('test');
        $this->assertEquals('test', InfluxDBManager::getDefaultClientName());
    }

    public function testGetClient(): void
    {
        $client = InfluxDBManager::getClient();
        $this->assertNotNull($client);
    }

    public function testGetDatabase(): void
    {
        $database = InfluxDBManager::getDatabase();
        $this->assertEquals(getenv('INFLUXDB_TEST_ORM_DB') ?: 'db_influxdb_orm_ormtest', $database->getName());
        $this->assertTrue($database->exists());

        $database = InfluxDBManager::getDatabase('db2');
        $this->assertEquals('db2', $database->getName());
        $this->assertTrue($database->exists());
    }
}
