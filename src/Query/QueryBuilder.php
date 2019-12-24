<?php
namespace Yurun\InfluxDB\ORM\Query;

use Yurun\InfluxDB\ORM\InfluxDBManager;
use Yurun\InfluxDB\ORM\Client\ResultSet;

class QueryBuilder
{
    /**
     * 模型类名
     *
     * @var string
     */
    private $modelClass;

    /**
     * 模型元数据
     *
     * @var \Yurun\InfluxDB\ORM\Meta\Meta
     */
    private $modelMeta;

    /**
     * 表名
     *
     * @var string
     */
    private $table;

    /**
     * 字段
     *
     * @var array
     */
    private $fields = [];

    /**
     * where 条件
     *
     * @var string[]
     */
    private $where = [];

    /**
     * 排序
     *
     * @var array
     */
    private $orderBy = [];

    /**
     * 分组
     *
     * @var array
     */
    private $groupBy = [];

    /**
     * 数据库对象
     *
     * @var \InfluxDB\Database
     */
    private $database;

    /**
     * 时区
     *
     * @var string
     */
    private $timezone;

    /**
     * 构造方法赋值的时区
     *
     * @var string
     */
    private $originTimezone;

    /**
     * 最后执行的SQL语句
     *
     * @var string
     */
    private $lastSql;

    /**
     * 查询开始位置
     *
     * @var int
     */
    private $offset;

    /**
     * 限制数量
     *
     * @var int
     */
    private $limit;

    public function __construct(?string $clientName = null, ?string $databaseName = null, ?string $timezone = null)
    {
        $this->database = InfluxDBManager::getDatabase($databaseName, $clientName);
        $this->originTimezone = $this->timezone = $timezone;
    }

    /**
     * 从模型创建查询器
     *
     * @param string $modelClass
     * @return static
     */
    public static function createFromModel(string $modelClass): self
    {
        /** @var \Yurun\InfluxDB\ORM\Meta\Meta $meta */
        $meta = $modelClass::__getMeta();
        $obj = new static($meta->getClient(), $meta->getDatabase(), $meta->getTimezone());
        $obj->modelMeta = $meta;
        $obj->modelClass = $modelClass;
        return $obj;
    }

    /**
     * 字段
     *
     * @param string $field
     * @return static
     */
    public function field(string $field): self
    {
        $this->fields[] = $field;
        return $this;
    }

    /**
     * 表
     *
     * @param string $field
     * @return static
     */
    public function from(string $table): self
    {
        return $this->table($table);
    }

    /**
     * 表
     *
     * @param string $field
     * @return static
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 查询条件-AND
     *
     * @param string|array $field
     * @param string|null $op
     * @param mixed $value
     * @param string $condition
     * @return static
     */
    public function where($field, ?string $op = null, $value = null, string $condition = 'AND'): self
    {
        if(is_array($field))
        {
            $first = true;
            // 数组条件，无视后面的参数
            foreach($field as $k => $v)
            {
                $where = $k . ' = ' . $this->parseValue($k, $v);
                if($first)
                {
                    $this->where[] = ($this->where ? ($condition . ' ') : '') . $where;
                    $first = false;
                }
                else
                {
                    $this->where[] = 'AND ' . $where;
                }
            }
        }
        else if(null === $op && null === $value && is_string($field))
        {
            // 原始sql语句
            $this->where[] = ($this->where ? ($condition . ' ') : '') . $field;
        }
        else
        {
            $where = $field . ' ' . $op . ' ' . $this->parseValue($field, $value);
            $this->where[] = ($this->where ? ($condition . ' ') : '') . $where;
        }
        return $this;
    }

    /**
     * 查询条件-OR
     *
     * @param string|array $field
     * @param string|null $op
     * @param mixed $value
     * @return static
     */
    public function orWhere($field, ?string $op = null, $value = null): self
    {
        return $this->where($field, $op, $value, 'OR');
    }

    /**
     * 排序
     *
     * @param string $field
     * @param string|null $order
     * @return static
     */
    public function order(string $field, ?string $order = null): self
    {
        $this->orderBy[] = $field . ($order ? (' ' . $order) : '');
        return $this;
    }

    /**
     * 分组
     *
     * @param string $group
     * @return static
     */
    public function group(string $group): self
    {
        $this->groupBy[] = $group;
        return $this;
    }

    /**
     * 限制起始位置和条数
     *
     * @param int $offset
     * @param int|null $limit
     * @return static
     */
    public function limit(int $offset, ?int $limit = null): self
    {
        if(null === $limit)
        {
            $this->offset = null;
            $this->limit = $offset;
        }
        else
        {
            $this->offset = $offset;
            $this->limit = $limit;
        }
        return $this;
    }

    /**
     * 时区
     *
     * @param string|null $timezone
     * @return static
     */
    public function timezone(?string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * 处理加入条件的值
     *
     * @param string $field
     * @param mixed $value
     * @return string
     */
    private function parseValue(string $field, $value): string
    {
        if($this->modelMeta && $property = $this->modelMeta->getByFieldName($field))
        {
            if($property->isTag())
            {
                // 标签永远是字符串
                return "'{$value}'";
            }
            else if($property->isField())
            {
                switch($property->getFieldType())
                {
                    case 'int':
                    case 'integer':
                    case 'float':
                    case 'double':
                        return $value;
                    case 'bool':
                    case 'boolean':
                        return json_encode(!!$value);
                    default:
                        // 默认作为字符串处理
                        return "'{$value}'";
                }
            }
            else if($property->isValue())
            {
                return $value;
            }
            else if($property->isTimestamp())
            {
                if(is_numeric($value))
                {
                    return $value;
                }
                else
                {
                    return "'{$value}'";
                }
            }
        }
        else
        {
            if(is_string($value))
            {
                return "'{$value}'";
            }
            else if(is_bool($value))
            {
                return json_encode(!!$value);
            }
            else
            {
                return $value;
            }
        }
    }

    /**
     * 构建 SQL 语句
     *
     * @return string
     */
    public function buildSql(): string
    {
        if(!$this->table && $this->modelMeta)
        {
            $this->table = $this->modelMeta->getMeasurement();
        }
        $fields = $this->fields ? implode(',', $this->fields) : '*';
        $table = $this->table;
        if($this->where)
        {
            $where = ' where ' . implode(' ', $this->where);
        }
        else
        {
            $where = '';
        }
        $order = $this->orderBy ? (' order by ' . implode(',', $this->orderBy)) : '';
        $group = $this->groupBy ? (' group by ' . implode(',', $this->groupBy)) : '';
        if($this->timezone)
        {
            $tz = " tz('{$this->timezone}')";
        }
        else
        {
            $tz = '';
        }
        $limit = '';
        if(null === $this->offset && null !== $this->limit)
        {
            $limit = ' limit ' . $this->limit;
        }
        else if(null !== $this->offset && null !== $this->limit)
        {
            $limit = ' limit ' . $this->limit .' offset ' . $this->offset;
        }
        $sql = <<<SQL
select {$fields} from {$table}{$where}{$group}{$order}{$limit}{$tz}
SQL;
        $this->table = $this->offset = $this->limit = null;
        $this->where = [];
        $this->fields = [];
        $this->orderBy = [];
        $this->groupBy = [];
        $this->timezone = $this->originTimezone;
        return $sql;
    }

    /**
     * 查询
     *
     * @return \Yurun\InfluxDB\ORM\Client\ResultSet
     */
    public function select(): ResultSet
    {
        $sql = $this->lastSql = $this->buildSql();
        return $this->database->query($sql);
    }

    /**
     * Get 最后执行的SQL语句
     *
     * @return string
     */ 
    public function getLastSql(): string
    {
        return $this->lastSql;
    }

}
