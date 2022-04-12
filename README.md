# influxdb-orm

[![Latest Version](https://img.shields.io/packagist/v/yurunsoft/influxdb-orm.svg)](https://packagist.org/packages/yurunsoft/influxdb-orm)
[![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg)](https://secure.php.net/)
[![IMI License](https://img.shields.io/github/license/yurunsoft/influxdb-orm.svg)](https://github.com/yurunsoft/influxdb-orm/blob/master/LICENSE)

## 介绍

一个用于 InfluxDB 时序数据库的 ORM，终结没有 InfluxDB ORM 的时代。

常用操作一把梭，支持 php-fpm、Swoole 环境，一键轻松切换。

可以用于所有传统框架、所有 Swoole 框架中！

## Composer

本项目可以使用composer安装，遵循psr-4自动加载规则，在你的 `composer.json` 中加入下面的内容:

```json
{
    "require": {
        "yurunsoft/influxdb-orm": "~1.2.0"
    }
}
```

然后执行 `composer update` 安装。

## 使用

### Swoole 支持

无需做任何事情即可完美兼容 Swoole 环境！

### 定义模型

> 具体可参考 `example/test.php`

```php
<?php
namespace Yurun\InfluxDB\ORM\Example\Model;

use Yurun\InfluxDB\ORM\BaseModel;
use Yurun\InfluxDB\ORM\Annotation\Tag;
use Yurun\InfluxDB\ORM\Annotation\Field;
use Yurun\InfluxDB\ORM\Annotation\Value;
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

    /**
     * @Value
     *
     * @var int
     */
    private $value;

    public static function create($id, $name, $time, $value)
    {
        return new static(compact('id', 'name', 'time', 'value'));
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
     * Get the value of value
     *
     * @return int
     */ 
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @param int $value
     *
     * @return self
     */ 
    public function setValue(int $value)
    {
        $this->value = $value;

        return $this;
    }
}

```

### 数据写入

```php
use Yurun\InfluxDB\ORM\InfluxDBManager;

// 设置客户端名称为test，默认数据库为db_test
InfluxDBManager::setClientConfig('test', '127.0.0.1', 8086, '', '', false, false, 0, 0, 'db_test', '/');
// 设置默认数据库为test
InfluxDBManager::setDefaultClientName('test');

// 写入数据，支持对象和数组
$r = A::write([
    A::create(mt_rand(1, 999999), time(), time(), mt_rand(1, 100)),
    ['id'=>1, 'name'=>'aaa', 'time'=>time(), 'value'=>mt_rand(1, 100)],
]);

var_dump($r);
```

### 数据查询

```php
// 获取查询器
$query = A::query();

// 常见用法，反正就那一套，不多说了
$query->field('id,name')
      ->from('table')
      ->where([
          'id'    =>  1
      ])->where('id', '=', 1)
      ->orWhere('id', '=', 1)
      ->order('time', 'desc')
      ->group('id')
      ->limit(0, 10);

// 查询结果，与 InfluxDB 官方客户端一样用法
$resultSet = $query->select();

// 查询结果转模型，适合用于查询记录而不是统计数据
$model = $resultSet->getModel(A::class);

// 查询结果转模型列表，适合用于查询记录而不是统计数据
$list = $resultSet->getModelList(A::class);
```

### 模型快捷查询

适合用于查询记录而不是统计数据

```php
use Yurun\InfluxDB\ORM\Query\QueryBuilder;

// 查询结果转模型，适合用于查询记录而不是统计数据
$model = A::find(function(QueryBuilder $query){
    $query->where('id', '=', 1)->limit(1);
});

// 查询结果转模型列表，适合用于查询记录而不是统计数据
$list = A::select(function(QueryBuilder $query){
    $query->where('id', '=', 1)->limit(2);
});

```

### 获取单个字段值

```php
$count = A::query()->field('count(value)')->select()->getScalar();
```

## 免费技术支持

QQ群：17916227 [![点击加群](https://pub.idqqimg.com/wpa/images/group.png "点击加群")](https://jq.qq.com/?_wv=1027&k=5wXf4Zq)，如有问题会有人解答和修复。

## 运行环境

- [PHP](https://php.net/) >= 7.1
- [Composer](https://getcomposer.org/)

## 版权信息

`influxdb-orm` 遵循 MIT 开源协议发布，并提供免费使用。

## 捐赠

<img src="https://raw.githubusercontent.com/yurunsoft/influxdb-orm/master/res/pay.png"/>

开源不求盈利，多少都是心意，生活不易，随缘随缘……
