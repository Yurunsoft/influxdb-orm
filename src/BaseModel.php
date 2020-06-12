<?php
namespace Yurun\InfluxDB\ORM;

use InfluxDB\Point;
use Yurun\InfluxDB\ORM\Meta\Meta;
use Yurun\InfluxDB\ORM\Meta\MetaManager;
use Yurun\InfluxDB\ORM\Query\QueryBuilder;
use Yurun\InfluxDB\ORM\Util\DateTime;

/**
 * InfluxDB Model 基类
 */
abstract class BaseModel implements \JsonSerializable
{
    /**
     * Meta
     *
     * @var \Yurun\InfluxDB\ORM\Meta\Meta
     */
    private $__meta;

    /**
     * __construct
     * 
     * @param array $data 键需要是数据库存储的字段名
     */
    public function __construct(array $data = [])
    {
        $this->__meta = static::__getMeta();
        foreach($this->__meta->getProperties() as $propertyName => $property)
        {
            if($property->isField())
            {
                $name = $property->getFieldName();
            }
            else if($property->isTag())
            {
                $name = $property->getTagName();
            }
            else if($property->isTimestamp())
            {
                $name = 'time';
            }
            else if($property->isValue())
            {
                $name = 'value';
            }
            else
            {
                continue;
            }
            if(isset($data[$name]))
            {
                $this->$propertyName = $data[$name];
            }
            else if(isset($data[$propertyName]))
            {
                $this->$propertyName = $data[$propertyName];
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
     * @return static|null
     */
    public static function find(callable $callback): ?self
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
    public static function select(callable $callback): array
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
    public static function parseValue($value, string $type)
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

    /**
     * 获取格式化后的时间
     *
     * @param string|null $format
     * @return string
     */
    public function getFormatedTime(?string $format = null): string
    {
        $property = $this->__meta->getTimestamp();
        if(null === $format)
        {
            $format = $property->getTimeFormat();
        }
        $time = $this->__get($property->getName());
        if($format)
        {
            return DateTime::format($format, $time);
        }
        else
        {
            return $time;
        }
    }

    /**
     * 将当前对象作为数组返回
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        foreach($this->__meta->getProperties() as $propertyName => $property)
        {
            $result[$propertyName] = $this->__get($propertyName);
        }
        $result[$this->__meta->getTimestamp()->getName()] = $this->getFormatedTime();
        return $result;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
