<?php
namespace Yurun\InfluxDB\ORM\Meta;

use ReflectionClass;
use Yurun\InfluxDB\ORM\Annotation\Measurement;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Yurun\InfluxDB\ORM\Annotation\Field;
use Yurun\InfluxDB\ORM\Annotation\Tag;
use Yurun\InfluxDB\ORM\Annotation\Timestamp;
use Yurun\InfluxDB\ORM\Annotation\Value;

class Meta
{
    /**
     * 是否注册了加载器
     *
     * @var boolean
     */
    private static $isRegisterLoader = false;

    /**
     * 注解读取器
     *
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    private static $reader;

    /**
     * measurement
     *
     * @var string
     */
    private $measurement;

    /**
     * 客户端明
     *
     * @var string
     */
    private $client;
    
    /**
     * 数据库名
     *
     * @var string
     */
    private $database;

    /**
     * 属性列表
     *
     * @var \Yurun\InfluxDB\ORM\Meta\PropertyMeta[]
     */
    private $properties;

    /**
     * 字段名属性列表
     *
     * @var \Yurun\InfluxDB\ORM\Meta\PropertyMeta[]
     */
    private $propertiesByFieldName;

    /**
     * 标签列表
     *
     * @var \Yurun\InfluxDB\ORM\Meta\PropertyMeta[]
     */
    private $tags;

    /**
     * 字段列表
     *
     * @var \Yurun\InfluxDB\ORM\Meta\PropertyMeta[]
     */
    private $fields;

    /**
     * 时间戳
     *
     * @var \Yurun\InfluxDB\ORM\Meta\PropertyMeta
     */
    private $timestamp;

    /**
     * 时间戳记精度（默认为纳秒）。
     *
     * @var string
     */
    private $precision;

    /**
     * 值
     *
     * @var \Yurun\InfluxDB\ORM\Meta\PropertyMeta
     */
    private $value;

    /**
     * 指定写入所有点时要使用的显式保留策略。
     * 如果未设置，将使用默认保留期。
     * 这仅适用于Guzzle驱动程序。
     * UDP驱动程序利用服务器的influxdb配置文件中定义的端点配置。
     *
     * @var string
     */
    private $retentionPolicy;

    /**
     * 时区，用于查询时自动转换为当前时区的时间
     * 为空则使用 PHP 当前时区
     *
     * @var string
     */
    private $timezone;

    public function __construct($className)
    {
        if(!self::$isRegisterLoader)
        {
            AnnotationRegistry::registerLoader(function($class){
                return class_exists($class) || interface_exists($class);
            });
            self::$isRegisterLoader = true;
        }
        if(null === self::$reader)
        {
            self::$reader = new AnnotationReader();
        }
        $refClass = new ReflectionClass($className);
        /** @var Measurement $measurement */
        $measurement = self::$reader->getClassAnnotation($refClass, Measurement::class);
        if($measurement || !$measurement->name)
        {
            $this->measurement = $measurement->name;
            $this->client = $measurement->client;
            $this->database = $measurement->database;
            $this->retentionPolicy = $measurement->retentionPolicy;
            $this->timezone = $measurement->timezone ?? date_default_timezone_get();
        }
        else
        {
            throw new \InvalidArgumentException(sprintf('@Measurement must set the name property in Class %s', $className));
        }
        $properties = $propertiesByFieldName = $tags = $fields = [];
        $value = $timestamp = null;
        foreach($refClass->getProperties() as $property)
        {
            $name = $property->getName();
            $tagName = $tagType = $fieldName = $fieldType = null;
            $isTimestamp = $isValue = false;
            foreach(self::$reader->getPropertyAnnotations($property) as $annotation)
            {
                switch(get_class($annotation))
                {
                    case Tag::class:
                        /** @var Tag $annotation */
                        $tagName = $annotation->name;
                        $tagType = $annotation->type;
                        break;
                    case Field::class:
                        /** @var Field $annotation */
                        $fieldName = $annotation->name;
                        $fieldType = $annotation->type;
                        break;
                    case Timestamp::class:
                        /** @var Timestamp $annotation */
                        $isTimestamp = true;
                        $this->precision = $annotation->precision;
                        break;
                    case Value::class:
                        $isValue = true;
                        break;
                }
            }
            $propertyMeta = new PropertyMeta($name, $tagName, $tagType, $fieldName, $fieldType, $isTimestamp, $isValue);
            $properties[$name] = $propertyMeta;
            $propertiesByFieldName[$propertyMeta->getFieldName() ?? $propertyMeta->getTagName()] = $propertyMeta;
            if($propertyMeta->isTag())
            {
                $tags[$name] = $propertyMeta;
            }
            if($propertyMeta->isField())
            {
                $fields[$name] = $propertyMeta;
            }
            if($propertyMeta->isTimestamp())
            {
                $timestamp = $propertyMeta;
            }
            if($propertyMeta->isValue())
            {
                $value = $propertyMeta;
            }
        }
        $this->properties = $properties;
        $this->propertiesByFieldName = $propertiesByFieldName;
        $this->tags = $tags;
        $this->fields = $fields;
        if(null === $timestamp)
        {
            throw new \InvalidArgumentException(sprintf('Class %s must declared an @Timestamp property', $className));
        }
        $this->timestamp = $timestamp;
        $this->value = $value;
    }

    /**
     * Get the value of measurement
     */ 
    public function getMeasurement()
    {
        return $this->measurement;
    }

    /**
     * Get 属性列表
     *
     * @return \Yurun\InfluxDB\ORM\Meta\PropertyMeta[]
     */ 
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Get 属性
     *
     * @return \Yurun\InfluxDB\ORM\Meta\PropertyMeta|null
     */ 
    public function getProperty($name)
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * Get 属性
     *
     * @return \Yurun\InfluxDB\ORM\Meta\PropertyMeta|null
     */ 
    public function getByFieldName($fieldName)
    {
        return $this->propertiesByFieldName[$fieldName] ?? null;
    }

    /**
     * Get 标签列表
     *
     * @return \Yurun\InfluxDB\ORM\Meta\PropertyMeta[]
     */ 
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Get 字段列表
     *
     * @return \Yurun\InfluxDB\ORM\Meta\PropertyMeta[]
     */ 
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get 时间戳
     *
     * @return \Yurun\InfluxDB\ORM\Meta\PropertyMeta
     */ 
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Get 值
     *
     * @return \Yurun\InfluxDB\ORM\Meta\PropertyMeta
     */ 
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get 客户端明
     *
     * @return string
     */ 
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get 数据库名
     *
     * @return string
     */ 
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get uDP驱动程序利用服务器的influxdb配置文件中定义的端点配置。
     *
     * @return string
     */ 
    public function getRetentionPolicy()
    {
        return $this->retentionPolicy;
    }

    /**
     * Get 时间戳记精度（默认为纳秒）。
     *
     * @return string
     */ 
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * Get 为空则使用 PHP 当前时区
     *
     * @return string
     */ 
    public function getTimezone()
    {
        return $this->timezone;
    }

}
