<?php

use Yurun\InfluxDB\ORM\Example\Model\A;
use Yurun\InfluxDB\ORM\InfluxDBManager;

require dirname(__DIR__) . '/vendor/autoload.php';

InfluxDBManager::setClientConfig('db_test', '127.0.0.1', 8086, '', '', false, false, 0, 0, 'test');
InfluxDBManager::setDefaultClientName('db_test');

$r = A::write([
    A::create(mt_rand(1, 999999), time(), time()),
    ['id'=>1, 'name'=>'aaa', 'time'=>time()],
]);

var_dump($r);
