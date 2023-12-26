<?php

declare(strict_types=1);

namespace MaxieSystems\URL\DomainName;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Labels::class)]
#[UsesClass(\MaxieSystems\Exception\Messages::class)]
final class LabelsTest extends TestCase
{
    public function testToString(): void
    {
        $domain = 'www.example.com';
        $labels = new Labels($domain);
        $this->assertSame($domain, (string)$labels);
        $labels = new Labels($domain . '.');
        $this->assertSame($domain, (string)$labels);
    }

    public function testCount(): void
    {
        $domain = 'www.example.com';
        $labels = new Labels($domain);
        $this->assertCount(3, $labels);
        $domain = 'localhost';
        $labels = new Labels($domain);
        $this->assertCount(1, $labels);
        $domain = 'static.example.co.uk.';
        $labels = new Labels($domain);
        $this->assertCount(4, $labels);
    }

    public function testOffsetIsset(): void
    {
        $domain = 'www.example.com';
        $labels = new Labels($domain);
        foreach ([1, 2, -1] as $i) {
            $this->assertTrue(isset($labels[$i]));
        }
        foreach ([11, '2', -11, 'www'] as $i) {
            $this->assertNotTrue(isset($labels[$i]));
        }
    }

    public function testOffsetGet(): void
    {
        $domain = 'www.example.com';
        $labels = new Labels($domain);
        $this->assertSame('example', $labels[2]);
        $this->assertSame('www', $labels[-1]);
        $this->assertNull($labels[10]);
        $this->expectException(\TypeError::class);
        echo $labels['22'];
    }

    public function testOffsetSet(): void
    {
        $domain = 'www.example.com';
        $labels = new Labels($domain);
        $this->expectException(\Error::class);
        $labels[2] = 'xxx';
    }

    public function testOffsetUnset(): void
    {
        $domain = 'www.example.com';
        $labels = new Labels($domain);
        $this->expectException(\Error::class);
        unset($labels[2]);
    }

    public function testToArray(): void
    {
        $domain = 'www.example.com';
        $labels = new Labels($domain);
        $arr = $labels->toArray();
        $this->assertArrayHasKey(3, $arr);
        $this->assertArrayHasKey(2, $arr);
        $this->assertArrayHasKey(1, $arr);
        $this->assertSame('www', $arr[3]);
        $this->assertSame('example', $arr[2]);
        $this->assertSame('com', $arr[1]);
    }
}
