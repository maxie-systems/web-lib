<?php

declare(strict_types=1);

namespace MaxieSystems;

use PHPUnit\Framework\TestCase;

final class URLReadOnlyTest extends TestCase
{
    public function testConstruct(): void
    {
        $url = new URLReadOnly('https://example.com/');
        $this->assertSame('https', $url->scheme);
        $this->assertSame('example.com', $url->host);
        $this->assertSame('/', $url->path);
        $this->assertSame('', $url->query);
    }

    public function testClone(): void
    {
        $url = new URLReadOnly('https://example.com/');
        $this->expectException(\Error::class);
        $this->assertObjectHasProperty('url', clone $url);
    }

    public function testIsAbsolute(): void
    {
        foreach (
            [
            'https://msse2.maxtheps.beget.tech/index.php' => true,
            '/my-page.html' => false,
            '//msse2.maxtheps.beget.tech/?path=classes/URL/IsAbsolute.php' => true,
            'max.v.antipin@gmail.com' => false,
            'images/05/12/2023/pic-15.gif' => false,
            ] as $s => $v
        ) {
            $url = new URLReadOnly($s);
            $is_abs = $url->isAbsolute();
            if ($v) {
                $this->assertTrue($is_abs);
            } else {
                $this->assertNotTrue($is_abs);
            }
        }
    }

    public function testGetType(): void
    {
        foreach (
            [
            'https://msse2.maxtheps.beget.tech/index.php' => URLType::Absolute,
            '/my-page.html' => URLType::RootRelative,
            '//msse2.maxtheps.beget.tech/?path=classes/URL/IsAbsolute.php' => URLType::ProtocolRelative,
            'max.v.antipin@gmail.com' => URLType::Relative,
            'images/05/12/2023/pic-15.gif' => URLType::Relative,
            ] as $s => $expected
        ) {
            $url = new URLReadOnly($s);
            $this->assertSame($expected, $url->getType());
        }
    }

    public function testConstructFilter(): void
    {
        $url_str = 'https://example.com:8080/pictures/search.php?size=actual&nocompress#main-nav';
        $fragment = 'section-5';
        $url = new URLReadOnly($url_str, function (string $name, $value, \Closure $src_url, string $fragment = null) {
            if ('path' === $name) {
                return new URL\Path\Segments($value, [URL\Path\Segments::class, 'filterSegmentRaw']);
            } elseif ('query' === $name) {
                return new URL\Query($value);
            } elseif ('fragment' === $name) {
                return $fragment ?? $value;
            }
            return $value;
        }, $fragment);
        $this->assertSame('https', $url->scheme);
        $this->assertInstanceOf(URL\Path\Segments::class, $url->path);
        $this->assertInstanceOf(URL\Query::class, $url->query);
        $this->assertSame($fragment, $url->fragment);
    }
}
