<?php

declare(strict_types=1);

namespace MaxieSystems\URL;

use PHPUnit\Framework\TestCase;

final class DomainNameTest extends TestCase
{
    public function testCompare(): void
    {
        $dn = new DomainName('www.example.com');
        $dn->compare('example.com', $label);
        $this->assertSame('www', $label);
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
