<?php
namespace Yurun\InfluxDB\ORM\Example\Model;

use Yurun\InfluxDB\ORM\BaseModel;
use Yurun\InfluxDB\ORM\Annotation\Tag;
use Yurun\InfluxDB\ORM\Annotation\Field;
use Yurun\InfluxDB\ORM\Annotation\Timestamp;
use Yurun\InfluxDB\ORM\Annotation\Measurement;

/**
 * @Measurement(name="aaa")
 */
class A extends BaseModel
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
     * @Timestamp(precision="s")
     *
     * @var int|string
     */
    private $time;

    public static function create($id, $name, $time)
    {
        return new static(compact('id', 'name', 'time'));
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
}
