<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use InfluxDB\Client;

// init
(function()
{
    $client = new Client(getenv('INFLUXDB_HOST') ?: '127.0.0.1', getenv('INFLUXDB_PORT') ?: 8086, getenv('INFLUXDB_USERNAME') ?: '', getenv('INFLUXDB_PASSWORD') ?: '');
    $database = $client->selectDB(getenv('INFLUXDB_TEST_DB') ?: 'db_influxdb_orm');
    if($database->exists())
    {
        $database->drop();
    }
})();
