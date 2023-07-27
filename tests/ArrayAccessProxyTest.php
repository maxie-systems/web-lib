<?php

declare(strict_types=1);

namespace MaxieSystems;

use PHPUnit\Framework\TestCase;

final class ArrayAccessProxyTest extends TestCase
{
    public function testOffsetExists(): void
    {
        $obj = new \stdClass();
        $obj->name = 'Max';
        $arr = new ArrayAccessProxy($obj);
        $this->assertTrue(isset($arr['name']));
        $this->assertNotTrue(isset($arr['position']));
    }

    public function testOffsetGet(): void
    {
        $obj = new \stdClass();
        $obj->brand = 'Ferrari';
        $arr = new ArrayAccessProxy($obj);
        $this->assertSame($obj->brand, $arr['brand']);
    }

    public function testOffsetSet(): void
    {
        $obj = new \stdClass();
        $arr = new ArrayAccessProxy($obj, false);
        $arr['number'] = 333;
        $this->assertSame($obj->number, $arr['number']);
        $arr = new ArrayAccessProxy($obj);
        $this->expectException(\Error::class);
        $arr['number'] = 333;
    }

    public function testOffsetUnset(): void
    {
        $obj = new \stdClass();
        $obj->number = 333;
        $arr = new ArrayAccessProxy($obj, false);
        unset($arr['number']);
        $this->assertObjectNotHasProperty('number', $obj);
        $this->assertArrayNotHasKey('number', $arr);
        $arr = new ArrayAccessProxy($obj);
        $this->expectException(\Error::class);
        unset($arr['number']);
    }
}
