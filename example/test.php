<?php

use Yurun\InfluxDB\ORM\Example\Model\A;
use Yurun\InfluxDB\ORM\InfluxDBManager;
use Yurun\InfluxDB\ORM\Query\QueryBuilder;

require dirname(__DIR__) . '/vendor/autoload.php';

InfluxDBManager::setClientConfig('db_test', '127.0.0.1', 8086, '', '', false, false, 0, 0, 'test');
InfluxDBManager::setDefaultClientName('db_test');

$r = A::write([
    A::create(mt_rand(1, 999999), time(), time()),
    ['id'=>1, 'name'=>'aaa', 'time'=>time()],
]);

var_dump($r);

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
