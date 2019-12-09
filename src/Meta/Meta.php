<?php
namespace Yurun\InfluxDB\ORM\Meta;

use ReflectionClass;
use Yurun\InfluxDB\ORM\Annotation\Measurement;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Yurun\InfluxDB\ORM\Annotation\Field;
use Yurun\InfluxDB\ORM\Annotation\Tag;
use Yurun\InfluxDB\ORM\Annotation\Timestamp;

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
     * 属性列表
     *
     * @var \Yurun\InfluxDB\ORM\Meta\PropertyMeta[]
     */
    private $properties;

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
        }
        else
        {
            throw new \InvalidArgumentException(sprintf('@Measurement must set the name property in Class %s', $className));
        }
        $properties = $tags = $fields = [];
        $timestamp = null;
        foreach($refClass->getProperties() as $property)
        {
            $name = $property->getName();
            $tagName = $tagType = $fieldName = $fieldType = null;
            $isTimestamp = false;
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
                        $isTimestamp = true;
                        break;
                }
            }
            $propertyMeta = new PropertyMeta($name, $tagName, $tagType, $fieldName, $fieldType, $isTimestamp);
            $properties[$name] = $propertyMeta;
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
        }
        $this->properties = $properties;
        $this->tags = $tags;
        $this->fields = $fields;
        if(null === $timestamp)
        {
            throw new \InvalidArgumentException(sprintf('Class %s must declared an @Timestamp property', $className));
        }
        $this->timestamp = $timestamp;
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

}
