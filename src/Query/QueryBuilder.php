<?php

namespace Yurun\InfluxDB\ORM\Query;

use Yurun\InfluxDB\ORM\Client\ResultSet;
use Yurun\InfluxDB\ORM\InfluxDBManager;

class QueryBuilder
{
    /**
     * 模型类名.
     *
     * @var string
     */
    private $modelClass;

    /**
     * 模型元数据.
     *
     * @var \Yurun\InfluxDB\ORM\Meta\Meta|null
     */
    private $modelMeta;

    /**
     * 表名.
     *
     * @var string|null
     */
    private $table;

    /**
     * 字段.
     *
     * @var array
     */
    private $fields = [];

    /**
     * where 条件.
     *
     * @var string[]
     */
    private $where = [];

    /**
     * 排序.
     *
     * @var array
     */
    private $orderBy = [];

    /**
     * 分组.
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
     * 时区.
     *
     * @var string
     */
    private $timezone;

    /**
     * 构造方法赋值的时区.
     *
     * @var string
     */
    private $originTimezone;

    /**
     * 最后执行的SQL语句.
     *
     * @var string
     */
    private $lastSql;

    /**
     * 查询开始位置.
     *
     * @var int|null
     */
    private $offset;

    /**
     * 限制数量.
     *
     * @var int|null
     */
    private $limit;

    /**
     * 跟在 SQL 语句最后的代码
     *
     * @var string
     */
    private $last = '';

    public function __construct(?string $clientName = null, ?string $databaseName = null, ?string $timezone = null)
    {
        $this->database = InfluxDBManager::getDatabase($databaseName, $clientName);
        $this->originTimezone = $this->timezone = $timezone;
    }

    /**
     * 从模型创建查询器.
     *
     * @return static
     */
    public static function createFromModel(string $modelClass): self
    {
        /** @var \Yurun\InfluxDB\ORM\Meta\Meta $meta */
        $meta = $modelClass::__getMeta();
        // @phpstan-ignore-next-line
        $obj = new static($meta->getClient(), $meta->getDatabase(), $meta->getTimezone());
        $obj->modelMeta = $meta;
        $obj->modelClass = $modelClass;

        return $obj;
    }

    /**
     * 字段.
     *
     * @return static
     */
    public function field(string $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * 表.
     *
     * @return static
     */
    public function from(string $table): self
    {
        return $this->table($table);
    }

    /**
     * 表.
     *
     * @return static
     */
    public function table(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * 查询条件-AND.
     *
     * @param string|array $field
     * @param mixed        $value
     *
     * @return static
     */
    public function where($field, ?string $op = null, $value = null, string $condition = 'AND'): self
    {
        if (\is_array($field))
        {
            $first = true;
            // 数组条件，无视后面的参数
            foreach ($field as $k => $v)
            {
                $where = $k . ' = ' . $this->parseValue($k, $v);
                if ($first)
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
        elseif (null === $op && null === $value && \is_string($field))
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
     * 查询条件-OR.
     *
     * @param string|array $field
     * @param mixed        $value
     *
     * @return static
     */
    public function orWhere($field, ?string $op = null, $value = null): self
    {
        return $this->where($field, $op, $value, 'OR');
    }

    /**
     * 排序.
     *
     * @return static
     */
    public function order(string $field, ?string $order = null): self
    {
        $this->orderBy[] = $field . ($order ? (' ' . $order) : '');

        return $this;
    }

    /**
     * 分组.
     *
     * @return static
     */
    public function group(string $group): self
    {
        $this->groupBy[] = $group;

        return $this;
    }

    /**
     * 限制起始位置和条数.
     *
     * @return static
     */
    public function limit(int $offset, ?int $limit = null): self
    {
        if (null === $limit)
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
     * 时区.
     *
     * @return static
     */
    public function timezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * 跟在 SQL 语句最后的代码.
     *
     * @return static
     */
    public function last(string $last): self
    {
        $this->last = $last;

        return $this;
    }

    /**
     * 处理加入条件的值
     *
     * @param mixed $value
     */
    private function parseValue(string $field, $value): string
    {
        if ($this->modelMeta && ($property = $this->modelMeta->getByFieldName($field)))
        {
            if ($property->isTag())
            {
                // 标签永远是字符串
                return "'{$value}'";
            }
            elseif ($property->isField())
            {
                switch ($property->getFieldType())
                {
                    case 'int':
                    case 'integer':
                    case 'float':
                    case 'double':
                        return (string) $value;
                    case 'bool':
                    case 'boolean':
                        return json_encode((bool) $value);
                    default:
                        // 默认作为字符串处理
                        return "'{$value}'";
                }
            }
            elseif ($property->isValue())
            {
                return $value;
            }
            elseif ($property->isTimestamp())
            {
                if (is_numeric($value))
                {
                    return (string) $value;
                }
                else
                {
                    return "'{$value}'";
                }
            }

            return '';
        }
        elseif (\is_string($value))
        {
            return "'{$value}'";
        }
        elseif (\is_bool($value))
        {
            return json_encode((bool) $value);
        }
        else
        {
            return (string) $value;
        }
    }

    /**
     * 构建 SQL 语句.
     */
    public function buildSql(): string
    {
        if (!$this->table && $this->modelMeta)
        {
            $this->table = $this->modelMeta->getMeasurement();
        }
        $fields = $this->fields ? implode(',', $this->fields) : '*';
        $table = $this->table;
        if ($this->where)
        {
            $where = ' where ' . implode(' ', $this->where);
        }
        else
        {
            $where = '';
        }
        $order = $this->orderBy ? (' order by ' . implode(',', $this->orderBy)) : '';
        $group = $this->groupBy ? (' group by ' . implode(',', $this->groupBy)) : '';
        if ($this->timezone)
        {
            $tz = " tz('{$this->timezone}')";
        }
        else
        {
            $tz = '';
        }
        $limit = '';
        if (null === $this->offset && null !== $this->limit)
        {
            $limit = ' limit ' . $this->limit;
        }
        elseif (null !== $this->offset && null !== $this->limit)
        {
            $limit = ' limit ' . $this->limit . ' offset ' . $this->offset;
        }
        $last = $this->last;
        if ('' !== $last)
        {
            $last = ' ' . $last;
        }
        $sql = <<<SQL
select {$fields} from {$table}{$where}{$group}{$order}{$limit}{$last}{$tz}
SQL;
        $this->table = $this->offset = $this->limit = null;
        $this->where = [];
        $this->fields = [];
        $this->orderBy = [];
        $this->groupBy = [];
        $this->timezone = $this->originTimezone;
        $this->last = '';

        return $sql;
    }

    /**
     * 查询.
     */
    public function select(): ResultSet
    {
        $sql = $this->lastSql = $this->buildSql();

        // @phpstan-ignore-next-line
        return $this->database->query($sql);
    }

    /**
     * Get 最后执行的SQL语句.
     */
    public function getLastSql(): string
    {
        return $this->lastSql;
    }
}
