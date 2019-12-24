<?php
namespace Yurun\InfluxDB\ORM\Client;

use Yurun\InfluxDB\ORM\BaseModel;

class ResultSet extends \InfluxDB\ResultSet
{
    /**
     * 获取行数据
     *
     * @param integer $rowIndex
     * @return array|null
     */
    public function getRow(int $rowIndex = 0): ?array
    {
        $points = $this->getPoints();
        return $points[$rowIndex] ?? null;
    }

    /**
     * 获取值
     *
     * @param integer $columnIndex
     * @param integer $queryIndex
     * @param mixed $default
     * @return mixed
     */
    public function getScalar(int $columnIndex = 1, int $rowIndex = 0, $default = null)
    {
        $points = $this->getPoints();
        $row = $points[$rowIndex] ?? null;
        if(!$row)
        {
            return $default;
        }
        if(is_int($columnIndex))
        {
            $keys = array_keys($row);
            if(isset($keys[$columnIndex]))
            {
                return $row[$keys[$columnIndex]];
            }
            else
            {
                return $default;
            }
        }
        else
        {
            return $row[$columnIndex] ?? $default;
        }
    }

    /**
     * 获取模型
     *
     * @param string $modelClass
     * @param int $rowIndex
     * @return \Yurun\InfluxDB\ORM\BaseModel|null
     */
    public function getModel(string $modelClass, int $rowIndex = 0): ?BaseModel
    {
        $row = $this->getPoints()[$rowIndex] ?? null;
        if(!$row)
        {
            return null;
        }
        return new $modelClass($row);
    }

    /**
     * 获取模型列表
     *
     * @param string $modelClass
     * @return \Yurun\InfluxDB\ORM\BaseModel[]
     */
    public function getModelList(string $modelClass): array
    {
        $list = [];
        foreach($this->getPoints() as $point)
        {
            $list[] = new $modelClass($point);
        }
        return $list;
    }

}
