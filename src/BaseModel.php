<?php
namespace Yurun\InfluxDB\ORM;

use Yurun\InfluxDB\ORM\Meta\MetaManager;

/**
 * InfluxDB Model 基类
 */
abstract class BaseModel
{
    /**
     * 获取模型元数据
     *
     * @return \Yurun\InfluxDB\ORM\Meta\Meta
     */
    public static function __getMeta()
    {
        return MetaManager::get(static::class);
    }

}
