<?php

namespace Yurun\InfluxDB\ORM\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Value
{
    /**
     * 数据类型.
     *
     * @Enum({"string", "int", "integer", "float", "double", "bool", "boolean"})
     *
     * @var string
     */
    public $type = 'string';
}
