<?php
namespace Yurun\InfluxDB\ORM\Test\Tests;

use PHPUnit\Framework\TestCase;
use Yurun\InfluxDB\ORM\Test\Model\ModelA;
use Yurun\InfluxDB\ORM\Query\QueryBuilder;

class ModelTest extends TestCase
{
    public function testWriteWithObject()
    {
        $a = new ModelA([
            'id'    =>  1,
            'name'  =>  'a',
            'time'  =>  strtotime('2018-11-11 11:11:11'),
            'value' =>  11.11,
        ]);
        $b = new ModelA;
        $b->setId(2);
        $b->name = 'b';
        $b->time = strtotime('2019-11-11 11:11:11');
        $b->score = 1.1;
        $this->assertTrue(ModelA::write([
            $a,
            $b,
        ]));
    }

    public function testWriteWithArray()
    {
        $a = [
            'id'    =>  11,
            'name'  =>  'aa',
            'time'  =>  strtotime('2018-11-11 11:11:11'),
            'score' =>  11,
        ];
        $b = [
            'id'    =>  12,
            'name'  =>  'bb',
            'time'  =>  strtotime('2018-11-11 11:11:12'),
            'score' =>  12,
        ];
        $this->assertTrue(ModelA::write([
            $a,
            $b,
        ]));
    }

    public function testWriteWithMixed()
    {
        $a = [
            'id'    =>  21,
            'name'  =>  'aaa',
            'time'  =>  strtotime('2018-11-11 11:11:21'),
            'score' =>  21,
        ];
        $b = new ModelA([
            'id'    =>  22,
            'name'  =>  'bbb',
            'time'  =>  strtotime('2018-11-11 11:11:22'),
            'value' =>  22,
        ]);
        $this->assertTrue(ModelA::write([
            $a,
            $b,
        ]));
    }

    public function testFind()
    {
        $data = ModelA::find(function(QueryBuilder $query){
            $query->where([
                'id'    =>  1
            ]);
        });
        $this->assertEquals([
            'id'    =>  1,
            'name'  =>  'a',
            'time'  =>  '2018-11-11 11:11:11',
            'score' =>  11.11,
        ], $data->toArray());
    }

    public function testSelect()
    {
        $list = ModelA::select(function(QueryBuilder $query){
            $query->where([
                'time'  =>  '2018-11-11 11:11:11',
            ]);
        });
        $this->assertEquals([
            [
                'id'    =>  1,
                'name'  =>  'a',
                'time'  =>  '2018-11-11 11:11:11',
                'score' =>  11.11,
            ],
            [
                'id'    =>  11,
                'name'  =>  'aa',
                'time'  =>  '2018-11-11 11:11:11',
                'score' =>  11,
            ],
        ], json_decode(json_encode($list), true));
    }

}
