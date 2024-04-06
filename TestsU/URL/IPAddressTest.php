<?php

declare(strict_types=1);

namespace MaxieSystems\Tests\URL;

use MaxieSystems\URL\Exception\InvalidIPAddressException;
use MaxieSystems\URL\Host;
use MaxieSystems\URL\IPAddress;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IPAddress::class)]
#[UsesClass(Host::class)]
final class IPAddressTest extends TestCase
{
    public static function ipV6DataProvider(): array
    {
        return [
            ['[2001:db8:85a3:8d3:1319:8a2e:370:7348]'],
            ['[1080:0:0:0:8:800:200C:417A]'],
            ['[3ffe:2a00:100:7031::1]'],
            ['1080::8:800:200C:417A'],
            ['[::192.9.5.5]'],
            ['[::FFFF:129.144.52.38]'],
            ['[2010:836B:4179::836B:4179]'],
        ];
    }

    public static function ipV4DataProvider(): array
    {
        return [
            ['127.0.0.1'],
            ['188.187.142.208'],
        ];
    }

    #[DataProvider('ipV6DataProvider')]
    public function testV6(string $host): void
    {
        $ip = new IPAddress($host);
        $this->assertTrue($ip->v6);
        $host = trim($host, '[]');
        $this->assertSame($host, $ip->value);
        $this->assertSame("[$host]", (string)$ip);
    }

    #[DataProvider('ipV4DataProvider')]
    public function testNotV6(string $host): void
    {
        $ip = new IPAddress($host);
        $this->assertNotTrue($ip->v6);
        $this->assertSame($host, $ip->value);
        $this->assertSame($host, (string)$ip);
    }

    public function testInvalid(): void
    {
        $this->expectException(InvalidIPAddressException::class);
        echo new IPAddress('xxx|xxx');
    }

    public function testDebugInfo(): void
    {
        $ip = new IPAddress('127.0.0.1');
        $info = $ip->__debugInfo();
        $this->assertArrayHasKey('value', $info);
        $this->assertArrayHasKey('v6', $info);
    }
}
