<?php

declare(strict_types=1);

namespace MaxieSystems\Tests\URL;

use MaxieSystems\URL\DomainName;
use MaxieSystems\URL\Exception\InvalidDomainNameException;
use MaxieSystems\URL\Host;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DomainName::class)]
#[UsesClass(DomainName\Labels::class)]
#[UsesClass(Host::class)]
#[UsesClass(\MaxieSystems\Exception\Messages::class)]
final class DomainNameTest extends TestCase
{
    public function testInvalid(): void
    {
        $this->expectException(InvalidDomainNameException::class);
        echo new DomainName('');
    }

    public static function compareDataProvider(): array
    {
        return [
            ['www.example.com', 'example.com', 1, 'www'],
            ['example.org', 'www.example.org', -1, 'www'],
            ['example.com', 'example.com', 0, ''],
            ['www.example.net', 'static.example.co.uk', false, null]
        ];
    }

    #[DataProvider('compareDataProvider')]
    public function testCompare(string $domain1, string $domain2, int|false $expected, ?string $expected_label): void
    {
        $dn = new DomainName($domain1);
        foreach ([$domain2, new DomainName($domain2)] as $d) {
            $res = $dn->compare($d, $label);
            $this->assertSame($expected, $res);
            $this->assertSame($expected_label, $label);
        }
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
