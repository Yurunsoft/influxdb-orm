<?php
namespace Yurun\InfluxDB\ORM\Meta;

class PropertyMeta
{
    /**
     * 字段属性名
     *
     * @var string
     */
    private $name;

    /**
     * 标签名
     *
     * @var string|null
     */
    private $tagName;

    /**
     * 标签类型
     *
     * @var string|null
     */
    private $tagType;

    /**
     * 字段名
     *
     * @var string|null
     */
    private $fieldName;

    /**
     * 字段类型
     *
     * @var string|null
     */
    private $fieldType;

    /**
     * 是否为时间戳
     *
     * @var bool
     */
    private $timestamp;

    public function __construct($name, $tagName, $tagType, $fieldName, $fieldType, bool $timestamp)
    {
        $this->name = $name;
        $this->tagName = $tagName;
        $this->tagType = $tagType;
        $this->fieldName = $fieldName;
        $this->fieldType = $fieldType;
        $this->timestamp = $timestamp;
    }

    /**
     * Get 字段属性名
     *
     * @return string
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get 标签名
     *
     * @return string|null
     */ 
    public function getTagName()
    {
        return $this->tagName;
    }

    /**
     * Get 标签类型
     *
     * @return string|null
     */ 
    public function getTagType()
    {
        return $this->tagType;
    }

    /**
     * 是否为标签
     *
     * @return boolean
     */
    public function isTag()
    {
        return null !== $this->tagName;
    }

    /**
     * Get 字段名
     *
     * @return string|null
     */ 
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Get 字段类型
     *
     * @return string|null
     */ 
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * 是否为字段
     *
     * @return boolean
     */
    public function isField()
    {
        return null !== $this->fieldName;
    }

    /**
     * Get 是否为时间戳
     *
     * @return bool
     */ 
    public function isTimestamp()
    {
        return $this->timestamp;
    }

}
