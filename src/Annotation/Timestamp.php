<?php
namespace Yurun\InfluxDB\ORM\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Timestamp
{
    /**
     * 时间戳记精度（默认为纳秒）。
     *
     * @Enum({"n", "u", "ms", "s", "m", "h"})
     * @var string
     */
    public $precision = 'n';

}
