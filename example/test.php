<?php

use Yurun\InfluxDB\ORM\Example\Model\A;
use Yurun\InfluxDB\ORM\InfluxDBManager;
use Yurun\InfluxDB\ORM\Query\QueryBuilder;

require dirname(__DIR__) . '/vendor/autoload.php';

// 设置客户端名称为test，默认数据库为db_test
InfluxDBManager::setClientConfig('test', '127.0.0.1', 8086, '', '', false, false, 0, 0, 'db_test');
// 设置默认数据库为test
InfluxDBManager::setDefaultClientName('test');

// 写入数据，支持对象和数组
$r = A::write([
    A::create(mt_rand(1, 999999), time(), time(), mt_rand(1, 100)),
    ['id'=>1, 'name'=>'aaa', 'time'=>time(), 'value'=>mt_rand(1, 100)],
]);

var_dump($r);

// 获取查询器
$query = A::query();

$result = $query->where('id', '=', 1)->select();
var_dump($result->getPoints(), $query->getLastSql());

$result = $query->field('id,"name"')->where("id='553863'")->select();
var_dump($result->getPoints(), $query->getLastSql());

$result = $query->field('id,"name"')->order('time', 'desc')->select();
var_dump($result->getPoints(), $query->getLastSql());

$result = $query->field('id,"name"')->order('time', 'desc')->group('"id"')->select();
var_dump($result->getPoints(), $query->getLastSql());

$result = $query->where('id', '=', 1)->limit(1)->select();
var_dump($result->getPoints(), $query->getLastSql());

$result = $query->where('id', '=', 1)->limit(1, 2)->select();
var_dump($result->getPoints(), $query->getLastSql());


$result = $query->where('id', '=', 1)->limit(1, 2)->select();
var_dump($result->getModel(A::class));

$result = $query->where('id', '=', 1)->limit(1, 2)->select();
var_dump($result->getModelList(A::class));

var_dump(A::find(function(QueryBuilder $query){
    $query->where('id', '=', 1)->limit(1);
}));

var_dump(A::select(function(QueryBuilder $query){
    $query->where('id', '=', 1)->limit(2);
}));
