<?php
namespace Yurun\InfluxDB\ORM\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Measurement
{
    /**
     * 名称
     *
     * @var string
     */
    public $name;

    /**
     * 客户端名
     *
     * @var string
     */
    public $client;

    /**
     * 数据库名
     *
     * @var string
     */
    public $database;

    /**
     * 指定写入所有点时要使用的显式保留策略。
     * 如果未设置，将使用默认保留期。
     * 这仅适用于Guzzle驱动程序。
     * UDP驱动程序利用服务器的influxdb配置文件中定义的端点配置。
     *
     * @var string
     */
    public $retentionPolicy;

    /**
     * 时区，用于查询时自动转换为当前时区的时间
     * 为空则使用 PHP 当前时区
     *
     * @var string
     */
    public $timezone;

}
