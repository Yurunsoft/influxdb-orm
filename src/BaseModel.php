<?php
namespace Yurun\InfluxDB\ORM;

use InfluxDB\Point;
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

    /**
     * 构建 Points
     *
     * @param array $dataList
     * @return \InfluxDB\Point[]
     */
    public static function buildPoints($dataList)
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
                    $fields[$propertyName] = $item->$propertyName;
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
                    $fields[$propertyName] = $item[$propertyName];
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
    public static function write($dataList)
    {
        $points = static::buildPoints($dataList);
        
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
        return $methodName($name, $value);
    }

    public function __isset($name)
    {
        return null !== $this->__get($name);
    }

    public function __unset($name)
    {
    }

}
