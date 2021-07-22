<?php

namespace Yurun\InfluxDB\ORM\Meta;

class PropertyMeta
{
    /**
     * 字段属性名.
     *
     * @var string
     */
    private $name;

    /**
     * 标签名.
     *
     * @var string|null
     */
    private $tagName;

    /**
     * 标签类型.
     *
     * @var string|null
     */
    private $tagType;

    /**
     * 字段名.
     *
     * @var string|null
     */
    private $fieldName;

    /**
     * 字段类型.
     *
     * @var string|null
     */
    private $fieldType;

    /**
     * 值类型.
     *
     * @var string|null
     */
    private $valueType;

    /**
     * 是否为时间戳.
     *
     * @var bool
     */
    private $timestamp;

    /**
     * 是否为值
     *
     * @var bool
     */
    private $value;

    /**
     * 时间格式.
     *
     * @var string|null
     */
    private $timeFormat;

    public function __construct(string $name, ?string $tagName, ?string $tagType, ?string $fieldName, ?string $fieldType, ?string $valueType, ?string $timeFormat, bool $timestamp, bool $value)
    {
        $this->name = $name;
        $this->tagName = $tagName;
        $this->tagType = $tagType;
        $this->fieldName = $fieldName;
        $this->fieldType = $fieldType;
        $this->valueType = $valueType;
        $this->timeFormat = $timeFormat;
        $this->timestamp = $timestamp;
        $this->value = $value;
    }

    /**
     * Get 字段属性名.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get 标签名.
     */
    public function getTagName(): ?string
    {
        return $this->tagName;
    }

    /**
     * Get 标签类型.
     */
    public function getTagType(): ?string
    {
        return $this->tagType;
    }

    /**
     * 是否为标签.
     */
    public function isTag(): bool
    {
        return null !== $this->tagName;
    }

    /**
     * Get 字段名.
     */
    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    /**
     * Get 字段类型.
     */
    public function getFieldType(): ?string
    {
        return $this->fieldType;
    }

    /**
     * 是否为字段.
     */
    public function isField(): bool
    {
        return null !== $this->fieldName;
    }

    /**
     * Get 是否为时间戳.
     */
    public function isTimestamp(): bool
    {
        return $this->timestamp;
    }

    /**
     * Get 是否为值
     */
    public function isValue(): bool
    {
        return $this->value;
    }

    /**
     * Get 值类型.
     */
    public function getValueType(): ?string
    {
        return $this->valueType;
    }

    /**
     * Get 时间格式.
     */
    public function getTimeFormat(): ?string
    {
        return $this->timeFormat;
    }
}
