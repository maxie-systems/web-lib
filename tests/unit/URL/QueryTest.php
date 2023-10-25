<?php

declare(strict_types=1);

namespace MaxieSystems\URL;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Query::class)]
final class QueryTest extends TestCase
{
    public function testConstruct(): void
    {
        $qstr = 'a=5&b=7&c%5B%5D=10&c%5B%5D=20&c%5B%5D=30&c%5B%5D=40&c%5B%5D=50';
        $q = new Query($qstr);
        $this->assertCount(3, $q);
        $this->assertEquals(5, $q->a);
        $qarr = ['test' => 1, 'name' => 'Max', 'age' => 37, ];
        $q = new Query($qarr);
        $this->assertCount(3, $q);
        $this->assertEquals(37, $q['age']);
    }

    public function testOffsetGet(): void
    {
        $qstr = 'a=5&b=7&c%5B%5D=10&c%5B%5D=20&c%5B%5D=30&c%5B%5D=40&c%5B%5D=50';
        $q = new Query($qstr);
        $this->assertSame('5', $q['a']);
        $this->assertNull($q['aaa']);
        $q['c'][1] = 111;
        $this->assertSame(111, $q['c'][1]);
    }

    public function testOffsetAdd(): void
    {
        $q = new Query('');
        $this->expectException(\UnexpectedValueException::class);
        $q[] = 5;
    }

    public function testIterable(): void
    {
        $data = [
            'a' => 1, 'b' => 5, 'test1' => 'true', 'width' => 50,
            'filter' => ['price_min' => 500, 'price_max' => 5000],
        ];
        $q = new Query(http_build_query($data));
        foreach ($q as $k => $v) {
            $this->assertEquals($data[$k], $v);
        }
    }

    public function testCopy(): void
    {
        $q = new Query('aa=bb&ccc=15&d=sortable&order=DESC');
        $p = ['aaa' => 6, 'bb' => 7, 'ccc' => 'XXX'];
        $q->copy($p, 'aaa');
        $this->assertEquals(6, $q->aaa);
        $this->assertNotEquals(7, $q->bb);
        $this->assertNotEquals('XXX', $q->ccc);
        $q->copy('aaa=6&bb=7&ccc=XXX');
        $this->assertEquals(6, $q->aaa);
        $this->assertEquals(7, $q->bb);
        $this->assertEquals('XXX', $q->ccc);
    }

    public function testDelete(): void
    {
        $q = new Query('aa=bb&bb=7&ccc=15&d=sortable&order=DESC');
        $q->delete('aa', 'ccc', 'ddddd');
        $this->assertArrayNotHasKey('aa', $q);
        $this->assertArrayHasKey('bb', $q);
        $this->assertArrayNotHasKey('ccc', $q);
        $this->assertArrayHasKey('d', $q);
        $this->assertArrayNotHasKey('ddddd', $q);
        $this->assertArrayHasKey('order', $q);
        $this->assertCount(3, $q);
        $q->delete();
        $this->assertCount(0, $q);
    }

    public function testDeleteAllExcept(): void
    {
        $q = new Query('aa=bb&bb=7&ccc=15&d=sortable&order=DESC');
        $q->deleteAllExcept('aa', 'ccc', 'ddddd');
        $this->assertArrayHasKey('aa', $q);
        $this->assertArrayNotHasKey('bb', $q);
        $this->assertArrayHasKey('ccc', $q);
        $this->assertArrayNotHasKey('d', $q);
        $this->assertArrayNotHasKey('ddddd', $q);
        $this->assertArrayNotHasKey('order', $q);
        $this->assertCount(2, $q);
    }
}
