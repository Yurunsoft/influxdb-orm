<?php

namespace Yurun\InfluxDB\ORM\Test\Tests;

use PHPUnit\Framework\TestCase;
use Yurun\InfluxDB\ORM\Query\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    public function testSql(): void
    {
        $query = new QueryBuilder();
        $sql = $query->from('table1')
                    ->field('a,b,c')
                    ->group('g1')
                    ->order('a')
                    ->order('b', 'desc')
                    ->orWhere('a', '=', 1)
                    ->where('b', '=', 2)
                    ->orWhere([
                        'c'   => 3,
                        'd'   => 4,
                    ])
                    ->timezone('Asia/Shanghai')
                    ->limit(10, 100)
                    ->buildSql();
        $this->assertEquals(<<<SQL
select a,b,c from table1 where a = 1 AND b = 2 OR c = 3 AND d = 4 group by g1 order by a,b desc limit 100 offset 10 tz('Asia/Shanghai')
SQL
        , $sql);

        $sql = $query->table('table1')
                    ->field('a,b,c')
                    ->group('g1')
                    ->order('a')
                    ->order('b', 'desc')
                    ->orWhere('a', '=', 1)
                    ->where('b', '=', 2)
                    ->orWhere([
                        'c'   => 3,
                        'd'   => 4,
                    ])
                    ->limit(10)
                    ->buildSql();
        $this->assertEquals(<<<SQL
select a,b,c from table1 where a = 1 AND b = 2 OR c = 3 AND d = 4 group by g1 order by a,b desc limit 10
SQL
        , $sql);
    }
}
