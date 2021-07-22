<?php

namespace Yurun\InfluxDB\ORM\Meta;

abstract class MetaManager
{
    /**
     * 元数据集合.
     *
     * @var \Yurun\InfluxDB\ORM\Meta\Meta[]
     */
    private static $metas = [];

    /**
     * 获取模型元数据.
     *
     * @return \Yurun\InfluxDB\ORM\Meta\Meta
     */
    public static function get(string $className): Meta
    {
        if (isset(self::$metas[$className]))
        {
            return self::$metas[$className];
        }
        $meta = new Meta($className);

        return self::$metas[$className] = $meta;
    }
}
