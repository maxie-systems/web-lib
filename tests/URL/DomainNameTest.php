<?php

declare(strict_types=1);

namespace MaxieSystems\URL;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DomainName::class)]
#[UsesClass(DomainName\Labels::class)]
#[UsesClass(Host::class)]
#[UsesClass(\MaxieSystems\Exception\Messages::class)]
final class DomainNameTest extends TestCase
{
    public function testCompare(): void
    {
        $dn = new DomainName('www.example.com');
        $res = $dn->compare('example.com', $label);
        $this->assertSame(1, $res);
        $this->assertSame('www', $label);
        $dn = new DomainName('example.org');
        $res = $dn->compare('www.example.org', $label);
        $this->assertSame(-1, $res);
        $this->assertSame('www', $label);
        $dn = new DomainName('example.com');
        $dn1 = 'example.com';
        $res = $dn->compare($dn1, $label);
        $this->assertSame(0, $res);
        $this->assertSame('', $label);
        $res = $dn->compare(new DomainName($dn1), $label);
        $this->assertSame(0, $res);
        $this->assertSame('', $label);
        $dn = new DomainName('www.example.net');
        $dn1 = 'static.example.co.uk';
        $res = $dn->compare($dn1, $label);
        $this->assertFalse($res);
        $this->assertNull($label);
        $res = $dn->compare(new DomainName($dn1), $label);
        $this->assertFalse($res);
        $this->assertNull($label);
    }

    public function testToString(): void
    {
        $domain = 'www.example.com';
        $dn = new DomainName($domain);
        $this->assertSame($domain, (string)$dn);
        $dn = new DomainName($domain . '.');
        $this->assertSame($domain, (string)$dn);
    }

    public function testCount(): void
    {
        $domain = 'www.example.com';
        $dn = new DomainName($domain);
        $this->assertCount(3, $dn);
        $domain = 'localhost';
        $dn = new DomainName($domain);
        $this->assertCount(1, $dn);
    }

    public function testOffsetIsset(): void
    {
        $domain = 'www.example.com';
        $dn = new DomainName($domain);
        foreach ([1, 2, -1] as $i) {
            $this->assertTrue(isset($dn[$i]));
        }
        foreach ([11, '2', -11, 'www'] as $i) {
            $this->assertNotTrue(isset($dn[$i]));
        }
    }

    public function testOffsetGet(): void
    {
        $domain = 'www.example.com';
        $dn = new DomainName($domain);
        $this->assertSame('example', $dn[2]);
        $this->assertSame('www', $dn[-1]);
        $this->assertNull($dn[10]);
        $this->expectException(\TypeError::class);
        echo $dn['22'];
    }

    public function testOffsetSet(): void
    {
        $domain = 'www.example.com';
        $dn = new DomainName($domain);
        $this->expectException(\Error::class);
        $dn[2] = 'xxx';
    }

    public function testOffsetUnset(): void
    {
        $domain = 'www.example.com';
        $dn = new DomainName($domain);
        $this->expectException(\Error::class);
        unset($dn[2]);
    }
}
