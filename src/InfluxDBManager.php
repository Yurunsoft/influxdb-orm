<?php
namespace Yurun\InfluxDB\ORM;

use InfluxDB\Client;
use Yurun\InfluxDB\ORM\Client\YurunHttpDriver;

abstract class InfluxDBManager
{
    /**
     * 默认客户端名
     *
     * @var string
     */
    private static $defaultClientName;

    /**
     * 客户端配置
     *
     * @var array
     */
    private static $clientConfigs = [];

    /**
     * 客户端集合
     *
     * @var \Yurun\InfluxDB\ORM\Client\Client[]
     */
    private static $clients = [];

    /**
     * 数据库列表
     *
     * @var \InfluxDB\Database[]
     */
    private static $databases = [];

    /**
     * 设置客户端配置
     *
     * @param string $clientName
     * @param string $host
     * @param integer $port
     * @param string $username
     * @param string $password
     * @param boolean $ssl
     * @param boolean $verifySSL
     * @param integer $timeout
     * @param integer $connectTimeout
     * @param string $defaultDatabase
     * @return void
     */
    public static function setClientConfig($clientName, $host, $port = 8086, $username = '', $password = '', $ssl = false, $verifySSL = false, $timeout = 0, $connectTimeout = 0, $defaultDatabase = null)
    {
        static::$clientConfigs[$clientName] = compact('host', 'port', 'username', 'password', 'ssl', 'verifySSL', 'timeout', 'connectTimeout', 'defaultDatabase');
    }

    /**
     * 获取客户端配置
     *
     * @param string|null $clientName
     * @return array
     */
    public static function getClientConfig($clientName = null)
    {
        $clientName = static::getClientName($clientName);
        return static::$clientConfigs[$clientName] ?? null;
    }

    /**
     * 移除客户端配置
     *
     * @param string|null $clientName
     * @return void
     */
    public static function removeClientConfig($clientName = null)
    {
        $clientName = static::getClientName($clientName);
        if(isset(static::$clientConfigs[$clientName]))
        {
            unset(static::$clientConfigs[$clientName]);
        }
    }

    /**
     * 设置默认客户端名
     *
     * @param string $clientName
     * @return void
     */
    public static function setDefaultClientName(string $clientName)
    {
        static::$defaultClientName = $clientName;
    }

    /**
     * 获取默认客户端名
     *
     * @return string|null
     */
    public static function getDefaultClientName()
    {
        return static::$defaultClientName;
    }

    /**
     * 获取 InfluxDB 客户端
     *
     * @param string|null $clientName
     * @return \Yurun\InfluxDB\ORM\Client\Client
     */
    public static function getClient($clientName = null)
    {
        $clientName = static::getClientName($clientName);
        if(isset(static::$clients[$clientName]))
        {
            return static::$clients[$clientName];
        }
        if(!isset(static::$clientConfigs[$clientName]))
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
     *
     * @param string|null $databaseName
     * @param string|null $clientName
     * @return \InfluxDB\Database
     */
    public static function getDatabase($databaseName = null, $clientName = null)
    {
        if(null === $databaseName)
        {
            $clientName = static::getClientName($clientName);
            if(!isset(static::$clientConfigs[$clientName]))
            {
                throw new \RuntimeException(sprintf('Client %s config does not found', $clientName));
            }
            $databaseName = static::$clientConfigs[$clientName]['defaultDatabase'];
        }
        if(isset(static::$databases[$clientName][$databaseName]))
        {
            return static::$databases[$clientName][$databaseName];
        }
        $client = static::getClient($clientName);
        static::$databases[$clientName][$databaseName] = $database = $client->selectDB($databaseName);
        if(!$database->exists())
        {
            $database->create();
        }
        return $database;
    }

    /**
     * 获取客户端名称
     *
     * @param string|null $clientName
     * @return string
     */
    public static function getClientName($clientName = null)
    {
        return $clientName ?: static::$defaultClientName;
    }

}
