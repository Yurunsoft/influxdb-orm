<?php

namespace Yurun\InfluxDB\ORM\Test\Tests;

use PHPUnit\Framework\TestCase;
use Yurun\InfluxDB\ORM\Util\DateTime;

class DateTimeTest extends TestCase
{
    public function test(): void
    {
        $this->assertEquals('2019-11-11 12:01:01 000 000000 000000000', DateTime::format('Y-m-d H:i:s v u {ns}', '2019-11-11T12:01:01Z'));
        $this->assertEquals('2019-11-11 12:01:01 123 123000 123000000', DateTime::format('Y-m-d H:i:s {ms} {us} {ns}', '2019-11-11T12:01:01.123Z'));
        $this->assertEquals('2019-11-11 12:01:01 123 123456 123456000', DateTime::format('Y-m-d H:i:s {ms} {us} {ns}', '2019-11-11T12:01:01.123456Z'));
        $this->assertEquals('2019-11-11 12:01:01 123 123456 123456780', DateTime::format('Y-m-d H:i:s {ms} {us} {ns}', '2019-11-11T12:01:01.12345678Z'));
        $this->assertEquals('2019-11-11 12:01:01 123 123456 123456789', DateTime::format('Y-m-d H:i:s {ms} {us} {ns}', '2019-11-11T12:01:01.123456789Z'));
        $this->assertEquals('2019-11-11 12:01:01 123 123456 123456789', DateTime::format('Y-m-d H:i:s {ms} {us} {ns}', '2019-11-11T12:01:01.1234567899Z'));
    }
}
