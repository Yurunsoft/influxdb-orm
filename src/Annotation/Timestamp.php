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
     *
     * @var string
     */
    public $precision = 'n';

    /**
     * 显示用的日期格式设置.
     *
     * 支持 date() 函数的以外，还支持 {ms}、{us}、{ns}
     *
     * @var string
     */
    public $format;
}
