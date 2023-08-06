<?php

declare(strict_types=1);

namespace MaxieSystems;

use PHPUnit\Framework\TestCase;

final class HostTest extends TestCase
{
    public function testIsIP(): void
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
            $this->assertTrue(URL\Host::isIP($host));
        }
        foreach (
            [
                '[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80',
                'example.com',
            ] as $host
        ) {
            $this->assertNotTrue(URL\Host::isIP($host));
        }
    }

    public function testIsDomainName(): void
    {
        foreach (
            [
                'example.com',
                'example.com.',
                'localhost',
                'static.example.com',
                'xn--80aac6chp.xn--p1ai',
                'www.xn--80aac6chp.xn--p1ai',
            ] as $host
        ) {
            $this->assertTrue(URL\Host::isDomainName($host));
        }
        foreach (
            [
                '[2001:db8:85a3:8d3:1319:8a2e:370:7348]',
                '[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80',
                '1080::8:800:200C:417A',
                '[::192.9.5.5]',
                '127.0.0.1',
                'static..example.com',
                '.example.com',
                'работа.рф',
            ] as $host
        ) {
            $this->assertNotTrue(URL\Host::isDomainName($host));
        }
    }

    public function testCreate(): void
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
            ] as $host
        ) {
            $this->assertInstanceOf(URL\IPAddress::class, URL\Host::create($host));
        }
        foreach (
            [
                'example.com',
                'xn--80aac6chp.xn--p1ai',
            ] as $host
        ) {
            $this->assertInstanceOf(URL\DomainName::class, URL\Host::create($host));
        }
        $this->expectException(Exception\URL\InvalidHostException::class);
        URL\Host::create('[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80');
    }
}
