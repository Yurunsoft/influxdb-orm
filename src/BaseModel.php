<?php
namespace Yurun\InfluxDB\ORM;

use InfluxDB\Point;
use Yurun\InfluxDB\ORM\Meta\Meta;
use Yurun\InfluxDB\ORM\Meta\MetaManager;
use Yurun\InfluxDB\ORM\Query\QueryBuilder;

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
            if(!($name = $property->getFieldName() ?? $property->getTagName()))
            {
                if($property->isTimestamp() || $property->isValue())
                {
                    $name = $property->getName();
                }
            }
            if(isset($data[$name]))
            {
                if($property->isTag())
                {
                    $this->$propertyName = static::parseValue($data[$name], $property->getTagType());
                }
                else if($property->isField())
                {
                    $this->$propertyName = static::parseValue($data[$name], $property->getFieldType());
                }
                else
                {
                    $this->$propertyName = $data[$name];
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
                    $tags[$property->getTagName()] = $item->$propertyName;
                }
                foreach($meta->getFields() as $propertyName => $property)
                {
                    $fields[$property->getFieldName()] = static::parseValue($item->$propertyName, $property->getFieldType());
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
                    $tags[$property->getTagName()] = $item[$propertyName];
                }
                foreach($meta->getFields() as $propertyName => $property)
                {
                    $fields[$property->getFieldName()] = static::parseValue($item[$propertyName], $property->getFieldType());
                }
                if($valueProperty)
                {
                    $value = $item[$valueProperty->getName()];
                }
                else
                {
                    $value = 0;
                }
                $timestamp = $item[$timestampProperty->getName()];
            }
            else
            {
                throw new \InvalidArgumentException(sprintf('Write datalist[%s] invalid', $i));
            }
            $value = static::parseValue($value, $valueProperty ? $valueProperty->getValueType() : 'string');
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
     * 获取模型查询器
     *
     * @return \Yurun\InfluxDB\ORM\Query\QueryBuilder
     */
    public static function query(): QueryBuilder
    {
        return QueryBuilder::createFromModel(static::class);
    }

    /**
     * 查询并返回当前模型实例对象
     * 回调仅有一个参数，类型为 \Yurun\InfluxDB\ORM\Query\QueryBuilder
     * 
     * @param callable $callback
     * @return static
     */
    public static function find(callable $callback)
    {
        $query = static::query();
        $callback($query);
        return $query->select()->getModel(static::class);
    }

    /**
     * 查询并返回当前模型实例对象数组
     * 回调仅有一个参数，类型为 \Yurun\InfluxDB\ORM\Query\QueryBuilder
     * 
     * @param callable $callback
     * @return static[]
     */
    public static function select($callback): array
    {
        $query = static::query();
        $callback($query);
        return $query->select()->getModelList(static::class);
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
