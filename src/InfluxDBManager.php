<?php

namespace Yurun\InfluxDB\ORM;

use Yurun\InfluxDB\ORM\Client\Client;
use Yurun\InfluxDB\ORM\Client\Database;
use Yurun\InfluxDB\ORM\Client\YurunHttpDriver;

abstract class InfluxDBManager
{
    /**
     * 默认客户端名.
     *
     * @var string
     */
    private static $defaultClientName;

    /**
     * 客户端配置.
     *
     * @var array
     */
    private static $clientConfigs = [];

    /**
     * 客户端集合.
     *
     * @var \Yurun\InfluxDB\ORM\Client\Client[]
     */
    private static $clients = [];

    /**
     * 数据库列表.
     *
     * @var \InfluxDB\Database[]
     */
    private static $databases = [];

    /**
     * 设置客户端配置.
     *
     * @param string $defaultDatabase
     *
     * @return void
     */
    public static function setClientConfig(string $clientName, string $host, int $port = 8086, string $username = '', string $password = '', bool $ssl = false, bool $verifySSL = false, int $timeout = 0, int $connectTimeout = 0, ?string $defaultDatabase = null)
    {
        static::$clientConfigs[$clientName] = compact('host', 'port', 'username', 'password', 'ssl', 'verifySSL', 'timeout', 'connectTimeout', 'defaultDatabase');
    }

    /**
     * 获取客户端配置.
     *
     * @return array
     */
    public static function getClientConfig(?string $clientName = null): ?array
    {
        $clientName = static::getClientName($clientName);

        return static::$clientConfigs[$clientName] ?? null;
    }

    /**
     * 移除客户端配置.
     *
     * @return void
     */
    public static function removeClientConfig(?string $clientName = null)
    {
        $clientName = static::getClientName($clientName);
        if (isset(static::$clientConfigs[$clientName]))
        {
            unset(static::$clientConfigs[$clientName]);
        }
    }

    /**
     * 设置默认客户端名.
     *
     * @return void
     */
    public static function setDefaultClientName(string $clientName)
    {
        static::$defaultClientName = $clientName;
    }

    /**
     * 获取默认客户端名.
     */
    public static function getDefaultClientName(): ?string
    {
        return static::$defaultClientName;
    }

    /**
     * 获取 InfluxDB 客户端.
     */
    public static function getClient(?string $clientName = null): Client
    {
        $clientName = static::getClientName($clientName);
        if (isset(static::$clients[$clientName]))
        {
            return static::$clients[$clientName];
        }
        if (!isset(static::$clientConfigs[$clientName]))
        {
            throw new \RuntimeException(sprintf('Client %s config does not found', $clientName));
        }
        $config = static::$clientConfigs[$clientName];
        $client = new Client($config['host'], $config['port'], $config['username'], $config['password'], $config['ssl'], $config['verifySSL'], $config['timeout'], $config['connectTimeout']);
        $client->setDriver(new YurunHttpDriver($client->getBaseURI()));

        return static::$clients[$clientName] = $client;
    }

    /**
     * 获取 InfluxDB 数据库对象
     */
    public static function getDatabase(?string $databaseName = null, ?string $clientName = null): Database
    {
        if (null === $databaseName)
        {
            $clientName = static::getClientName($clientName);
            if (!isset(static::$clientConfigs[$clientName]))
            {
                throw new \RuntimeException(sprintf('Client %s config does not found', $clientName));
            }
            $databaseName = static::$clientConfigs[$clientName]['defaultDatabase'];
        }
        if (isset(static::$databases[$clientName][$databaseName]))
        {
            return static::$databases[$clientName][$databaseName];
        }
        $client = static::getClient($clientName);
        static::$databases[$clientName][$databaseName] = $database = $client->selectDB($databaseName);
        if (!$database->exists())
        {
            $database->create();
        }

        return $database;
    }

    /**
     * 获取客户端名称.
     *
     * @return string
     */
    public static function getClientName(?string $clientName = null): ?string
    {
        return $clientName ?: static::$defaultClientName;
    }
}
