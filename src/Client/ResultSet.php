<?php
namespace Yurun\InfluxDB\ORM\Client;

class ResultSet extends \InfluxDB\ResultSet
{
    /**
     * 获取行数据
     *
     * @param integer $rowIndex
     * @return array|null
     */
    public function getRow($rowIndex = 0)
    {
        $points = $this->getPoints();
        return $points[$rowIndex] ?? null;
    }

    /**
     * 获取模型
     *
     * @param string $modelClass
     * @return \Yurun\InfluxDB\ORM\BaseModel|null
     */
    public function getModel($modelClass, $rowIndex = 0)
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
    public function getModelList($modelClass)
    {
        $list = [];
        foreach($this->getPoints() as $point)
        {
            $list[] = new $modelClass($point);
        }
        return $list;
    }

}
