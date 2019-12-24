<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Point;

// init
(function()
{
    $client = new Client(getenv('INFLUXDB_HOST') ?: '127.0.0.1', getenv('INFLUXDB_PORT') ?: 8086, getenv('INFLUXDB_USERNAME') ?: '', getenv('INFLUXDB_PASSWORD') ?: '');
    $database = $client->selectDB(getenv('INFLUXDB_TEST_ORM_DB') ?: 'db_influxdb_orm_ormtest');
    if($database->exists())
    {
        $database->drop();
    }

    $database = $client->selectDB(getenv('INFLUXDB_TEST_DB') ?: 'db_influxdb_orm_dbtest');
    if($database->exists())
    {
        $database->drop();
    }
    $database->create();
    $database->writePoints([
        new Point('a', 1, [
            'id'    =>  1,
        ], [
            'name'  =>  'a',
            'age'   =>  11,
        ], strtotime('2019-01-01 01:01:01')),
        new Point('a', 2, [
            'id'    =>  2,
        ], [
            'name'  =>  'b',
            'age'   =>  22,
        ], strtotime('2019-02-02 02:02:02')),
        new Point('a', 3, [
            'id'    =>  3,
        ], [
            'name'  =>  'a',
            'age'   =>  33,
        ], strtotime('2019-03-03 03:03:03')),
    ], Database::PRECISION_SECONDS);
})();
