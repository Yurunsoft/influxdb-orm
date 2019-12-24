<?php
namespace Yurun\InfluxDB\ORM\Test\Model;

use Yurun\InfluxDB\ORM\BaseModel;
use Yurun\InfluxDB\ORM\Annotation\Tag;
use Yurun\InfluxDB\ORM\Annotation\Field;
use Yurun\InfluxDB\ORM\Annotation\Value;
use Yurun\InfluxDB\ORM\Annotation\Timestamp;
use Yurun\InfluxDB\ORM\Annotation\Measurement;

/**
 * @Measurement(name="a")
 * @property int $id
 * @property string $name
 * @property int $time
 * @property int $score
 */
class ModelA extends BaseModel
{
    /**
     * @Tag(name="id", type="int")
     *
     * @var int
     */
    private $id;

    /**
     * @Field(name="name", type="string")
     *
     * @var string
     */
    private $name;

    /**
     * @Timestamp(precision="s", format="Y-m-d H:i:s")
     *
     * @var int|string
     */
    private $time;

    /**
     * @Value
     *
     * @var float
     */
    private $score;

    public static function create($id, $name, $time, $score)
    {
        return new static(compact('id', 'name', 'time', 'score'));
    }

    /**
     * Get the value of time
     *
     * @return int|string
     */ 
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set the value of time
     *
     * @param int|string $time
     *
     * @return self
     */ 
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get the value of id
     *
     * @return int
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param int $id
     *
     * @return self
     */ 
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of name
     *
     * @return string
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param string $name
     *
     * @return self
     */ 
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of score
     *
     * @return float
     */ 
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set the value of score
     *
     * @param float $score
     *
     * @return self
     */ 
    public function setScore(float $score)
    {
        $this->score = $score;

        return $this;
    }

}