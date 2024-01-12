<?php

declare(strict_types=1);

namespace MaxieSystems\URL;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Host::class)]
#[UsesClass(DomainName::class)]
#[UsesClass(DomainName\Labels::class)]
#[UsesClass(IPAddress::class)]
final class HostTest extends TestCase
{
    public static function ipsDataProvider(): array
    {
        return [
            ['[2001:db8:85a3:8d3:1319:8a2e:370:7348]'],
            ['[1080:0:0:0:8:800:200C:417A]'],
            ['[3ffe:2a00:100:7031::1]'],
            ['1080::8:800:200C:417A'],
            ['[::192.9.5.5]'],
            ['[::FFFF:129.144.52.38]'],
            ['[2010:836B:4179::836B:4179]'],
            ['127.0.0.1'],
            ['188.187.142.208'],
        ];
    }

    public static function notIPsDataProvider(): array
    {
        return [
            'IPv6 + port' => ['[FDDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80'],
            'Domain name' => ['example.com'],
        ];
    }

    public static function domainNamesDataProvider(): array
    {
        return [
            ['example.com'],
            ['example.com.'],
            ['localhost'],
            ['static.example.com'],
            ['xn--80aac6chp.xn--p1ai'],
            ['www.xn--80aac6chp.xn--p1ai'],
        ];
    }

    public static function notDomainNamesDataProvider(): array
    {
        return [
            ['[2001:db8:85a3:8d3:1319:8a2e:370:7347]'],
            ['[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80'],
            ['1080::8:800:200C:418A'],
            ['[::192.9.7.5]'],
            ['127.0.0.11'],
            ['static..example.com'],
            ['.example.com'],
            ['работа.рф'],
        ];
    }

    public static function domainsAndIPsDataProvider(): array
    {
        return [
            ['[2001:db8:85a3:8d3:1319:8a2e:370:7328]', IPAddress::class],
            ['[1080:0:0:0:8:800:200C:717A]', IPAddress::class],
            ['[3ffe:2a00:100:7031::1]', IPAddress::class],
            ['1080::8:800:200C:417A', IPAddress::class],
            ['[::192.9.5.6]', IPAddress::class],
            ['[::FFFF:129.144.52.38]', IPAddress::class],
            ['[2010:836B:4179::836B:4179]', IPAddress::class],
            ['127.0.0.2', IPAddress::class],
            ['example.com', DomainName::class],
            ['xn--80aac6chp.xn--p1ai', DomainName::class],
        ];
    }

    #[DataProvider('ipsDataProvider')]
    public function testIsIP(string $host): void
    {
        $this->assertTrue(Host::isIP($host));
    }

    #[DataProvider('notIPsDataProvider')]
    public function testIsNotIP(string $host): void
    {
        $this->assertNotTrue(Host::isIP($host));
    }

    #[DataProvider('domainNamesDataProvider')]
    public function testIsDomainName(string $host): void
    {
        $this->assertTrue(Host::isDomainName($host));
    }

    #[DataProvider('notDomainNamesDataProvider')]
    public function testIsNotDomainName(string $host): void
    {
        $this->assertNotTrue(Host::isDomainName($host));
    }

    #[DataProvider('domainsAndIPsDataProvider')]
    public function testCreate(string $host, string $expected_class): void
    {
        $this->assertInstanceOf($expected_class, Host::create($host));
    }

    public function testCreateFail(): void
    {
        $this->expectException(Exception\InvalidHostException::class);
        Host::create('[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80');
    }
}
