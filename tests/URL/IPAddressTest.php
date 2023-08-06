<?php

declare(strict_types=1);

namespace MaxieSystems;

use PHPUnit\Framework\TestCase;

final class IPAddressTest extends TestCase
{
    public function testValue(): void
    {
        foreach (
            [
                '[2001:db8:85a3:8d3:1319:8a2e:370:7348]',
                '[1080:0:0:0:8:800:200C:417A]',
                '[3ffe:2a00:100:7031::1]',
                '1080::8:800:200C:417A',
                '[::192.9.5.5]',
                '[::FFFF:129.144.52.38]',
                '[2010:836B:4179::836B:4179]',
                '127.0.0.1',
                '188.187.142.208',
            ] as $host
        ) {
            $ip = new URL\IPAddress($host);
            $this->assertSame(trim($host, '[]'), $ip->value);
        }
    }

    public function testV6(): void
    {
        foreach (
            [
                '[2001:db8:85a3:8d3:1319:8a2e:370:7348]',
                '[1080:0:0:0:8:800:200C:417A]',
                '[3ffe:2a00:100:7031::1]',
                '1080::8:800:200C:417A',
                '[::192.9.5.5]',
                '[::FFFF:129.144.52.38]',
                '[2010:836B:4179::836B:4179]',
            ] as $host
        ) {
            $ip = new URL\IPAddress($host);
            $this->assertTrue($ip->v6);
        }
        foreach (
            [
                '127.0.0.1',
                '188.187.142.208',
            ] as $host
        ) {
            $ip = new URL\IPAddress($host);
            $this->assertNotTrue($ip->v6);
        }
    }
}
