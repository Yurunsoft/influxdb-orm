<?php

namespace Yurun\InfluxDB\ORM\Test\Tests;

use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;
use function Swoole\Coroutine\run;
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
        $client1 = InfluxDBManager::getClient();
        $this->assertIsObject($client1);
        $client2 = InfluxDBManager::getClient();
        $this->assertIsObject($client2);
        $this->assertEquals(spl_object_hash($client1), spl_object_hash($client2));

        if (\defined('SWOOLE_VERSION'))
        {
            run(function () {
                $client1 = InfluxDBManager::getClient();
                $this->assertIsObject($client1);
                $client2 = InfluxDBManager::getClient();
                $this->assertIsObject($client2);
                $this->assertEquals(spl_object_hash($client1), spl_object_hash($client2));
            });
            $client1 = $client2 = null;
            run(function () use (&$client1, &$client2) {
                Coroutine::create(function () use (&$client1) {
                    $client1 = InfluxDBManager::getClient();
                });
                Coroutine::create(function () use (&$client2) {
                    $client2 = InfluxDBManager::getClient();
                });
            });
            $this->assertIsObject($client1);
            $this->assertIsObject($client2);
            $this->assertNotEquals(spl_object_hash($client1), spl_object_hash($client2));
        }
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
