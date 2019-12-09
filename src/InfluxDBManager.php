<?php
namespace Yurun\InfluxDB\ORM;

abstract class InfluxDBManager
{
    /**
     * 默认客户端名
     *
     * @var string
     */
    private static $defaultClientName;

    /**
     * 默认数据库名
     *
     * @var string
     */
    private static $defaultDatabase;

    /**
     * 客户端集合
     *
     * @var \InfluxDB\Client[]
     */
    private static $clients;

    /**
     * 数据库列表
     *
     * @var \InfluxDB\Database[]
     */
    private static $databases = [];

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
     * @return string
     */
    public static function getDefaultClientName(): string
    {
        return static::$defaultClientName;
    }

    /**
     * 设置默认数据库名
     *
     * @param string $databaseName
     * @return void
     */
    public static function setDefaultDatabase(string $databaseName)
    {
        static::$defaultDatabase = $databaseName;
    }

    /**
     * 获取默认数据库名
     *
     * @return string
     */
    public static function getDefaultDatabase(): string
    {
        return static::$defaultDatabase;
    }

    /**
     * 获取 InfluxDB 客户端
     *
     * @param string|null $clientName
     * @return \InfluxDB\Client
     */
    public static function getClient($clientName = null)
    {

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

    }

}
