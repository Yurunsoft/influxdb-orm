<?php
namespace Yurun\InfluxDB\ORM\Client;

class ResultSet extends \InfluxDB\ResultSet
{
    /**
     * 获取模型
     *
     * @param string $modelClass
     * @return \Yurun\InfluxDB\ORM\BaseModel
     */
    public function getModel($modelClass, $rowIndex = 0)
    {
        $row = $this->getPoints()[$rowIndex] ?? [];
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
