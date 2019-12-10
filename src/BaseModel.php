<?php
namespace Yurun\InfluxDB\ORM;

use InfluxDB\Point;
use Yurun\InfluxDB\ORM\Meta\Meta;
use Yurun\InfluxDB\ORM\Meta\MetaManager;

/**
 * InfluxDB Model 基类
 */
abstract class BaseModel
{
    public function __construct($data = [])
    {
        $meta = static::__getMeta();
        foreach($meta->getProperties() as $propertyName => $property)
        {
            if(isset($data[$propertyName]))
            {
                if($property->isTag())
                {
                    $this->$propertyName = static::parseValue($data[$propertyName], $property->getTagType());
                }
                else if($property->isField())
                {
                    $this->$propertyName = static::parseValue($data[$propertyName], $property->getFieldType());
                }
            }
        }
    }

    /**
     * 获取模型元数据
     *
     * @return \Yurun\InfluxDB\ORM\Meta\Meta
     */
    public static function __getMeta(): Meta
    {
        return MetaManager::get(static::class);
    }

    /**
     * 构建 Points
     *
     * @param array $dataList
     * @return \InfluxDB\Point[]
     */
    public static function buildPoints(array $dataList): array
    {
        $points = [];
        $meta = static::__getMeta();
        $valueProperty = $meta->getValue();
        $timestampProperty = $meta->getTimestamp();
        foreach($dataList as $i => $item)
        {
            $tags = $fields = [];
            if($item instanceof static)
            {
                foreach($meta->getTags() as $propertyName => $property)
                {
                    $tags[$propertyName] = $item->$propertyName;
                }
                foreach($meta->getFields() as $propertyName => $property)
                {
                    $fields[$propertyName] = static::parseValue($item->$propertyName, $property->getFieldType());
                }
                if($valueProperty)
                {
                    $value = $item->{$valueProperty->getName()};
                }
                else
                {
                    $value = 0;
                }
                $timestamp = $item->{$timestampProperty->getName()};
            }
            else if(is_array($item))
            {
                foreach($meta->getTags() as $propertyName => $property)
                {
                    $tags[$propertyName] = $item[$propertyName];
                }
                foreach($meta->getFields() as $propertyName => $property)
                {
                    $fields[$propertyName] = static::parseValue($item[$propertyName], $property->getFieldType());
                }
                $timestamp = $item[$timestampProperty->getName()];
            }
            else
            {
                throw new \InvalidArgumentException(sprintf('Write datalist[%s] invalid', $i));
            }
            $points[] = new Point($meta->getMeasurement(), $value, $tags, $fields, $timestamp);
        }
        return $points;
    }

    /**
     * 写入数据
     *
     * @param array $dataList
     * @return bool
     */
    public static function write(array $dataList): bool
    {
        $points = static::buildPoints($dataList);
        $meta = static::__getMeta();
        $database = InfluxDBManager::getDatabase($meta->getDatabase(), $meta->getClient());
        return $database->writePoints($points, $meta->getPrecision(), $meta->getRetentionPolicy());
    }

    /**
     * 处理值
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    public static function parseValue($value, $type)
    {
        switch($type)
        {
            case 'string':
                return (string)$value;
            case 'int':
            case 'integer':
                return (int)$value;
            case 'float':
            case 'double':
                return (float)$value;
            case 'bool':
            case 'boolean':
                return !!$value;
            default:
                return $value;
        }
    }

    public function &__get($name)
    {
        $methodName = 'get' . ucfirst($name);
        if(method_exists($this, $methodName))
        {
            $result = $this->$methodName();
        }
        else
        {
            $result = null;
        }
        return $result;
    }

    public function __set($name, $value)
    {
        $methodName = 'set' . ucfirst($name);
        return $this->$methodName($value);
    }

    public function __isset($name)
    {
        return null !== $this->__get($name);
    }

    public function __unset($name)
    {
    }

}
