<?php

declare(strict_types=1);

namespace MaxieSystems;

use MaxieSystems\URL\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(URLReadOnly::class)]
#[UsesClass(URL::class)]
#[UsesClass(URL\Path\Segments::class)]
#[UsesClass(URL\Path::class)]
#[UsesClass(Query::class)]
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
        $url = new URLReadOnly('https://example.net/');
        $this->expectException(\Error::class);
        $this->assertObjectHasProperty('url', clone $url);
    }

    public function testIsAbsolute(): void
    {
        foreach (
            [
                'https://msse2.maxtheps.beget.tech/index.php',
            ] as $s
        ) {
            $url = new URLReadOnly($s);
            $this->assertTrue($url->isAbsolute());
        }
        foreach (
            [
                '/my-page.html',
                '//msse2.maxtheps.beget.tech/?path=classes/URL/IsAbsolute.php',
                'max.v.antipin@gmail.com',
                'images/05/12/2023/pic-15.gif',
            ] as $s
        ) {
            $url = new URLReadOnly($s);
            $this->assertNotTrue($url->isAbsolute());
        }
    }

    public static function URLsWithTypesProvider(): array
    {
        return [
            'Absolute' => ['https://msse2.maxtheps.beget.tech/index.php', URLType::Absolute],
            'Root-relative' => ['/my-page.html', URLType::RootRelative],
            'Protocol-relative' => [
                '//msse2.maxtheps.beget.tech/?path=classes/URL/IsAbsolute.php',
                URLType::ProtocolRelative
            ],
            'Relative (email)' => ['max.v.antipin@gmail.com', URLType::Relative],
            'Relative' => ['images/05/12/2023/pic-15.gif', URLType::Relative],
            'Empty' => ['', URLType::Empty],
        ];
    }

    #[DataProvider('URLsWithTypesProvider')]
    public function testGetType(string $url, URLType $expected): void
    {
        $u = new URLReadOnly($url);
        $this->assertSame($expected, $u->getType());
    }

    public function testConstructOnCreate(): void
    {
        $url_str = 'https://example.com:8080/pictures/search.php?size=actual&nocompress#main-nav';
        $fragment = 'section-5';
        $url = new URLReadOnly(
            $url_str,
            function (URL $url, string $fragment = '') {
                $url->path = new URL\Path\Segments($url->path, null, URL\Path\Segments::FILTER_RAW);
                $url->query = new URL\Query($url->query);
                if ('' !== $fragment) {
                    $url->fragment = $fragment;
                }
            },
            $fragment
        );
        $this->assertSame('https', $url->scheme);
        $this->assertInstanceOf(URL\Path\Segments::class, $url->path);
        $this->assertInstanceOf(URL\Query::class, $url->query);
        $this->assertSame($fragment, $url->fragment);
    }

    public function testIsEmpty(): void
    {
        $url = new URLReadOnly('https://example.com/');
        $this->assertNotTrue($url->isEmpty());
        $this->assertNotTrue($url->isEmpty('authority'));
        $url = new URLReadOnly('');
        $this->assertTrue($url->isEmpty());
        $this->assertTrue($url->isEmpty('authority'));
        $this->expectException(\UnexpectedValueException::class);
        $url->isEmpty('netloc');
    }
}
