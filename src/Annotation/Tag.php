<?php

namespace Yurun\InfluxDB\ORM\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Tag
{
    /**
     * 名称.
     *
     * @var string
     */
    public $name;

    /**
     * 数据类型.
     *
     * @Enum({"string", "int", "integer", "float", "double", "bool", "boolean"})
     *
     * @var string
     */
    public $type = 'string';
}
