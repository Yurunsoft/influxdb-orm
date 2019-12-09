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

}
