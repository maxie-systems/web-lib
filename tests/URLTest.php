<?php

declare(strict_types=1);

namespace MaxieSystems;

use PHPUnit\Framework\TestCase;

final class URLTest extends TestCase
{
    public function testBuild(): void
    {
        $urls = [
            [
                'str' => '',
                'arr' => [
                    'scheme' => '',
                    'host' => '',
                    'port' => '',
                    'user' => '',
                    'pass' => '',
                    'path' => '',
                    'query' => '',
                    'fragment' => ''
                ],
            ],
            [
                'str' => 'https://example.com/#nav',
                'arr' => [
                    'scheme' => 'https',
                    'host' => 'example.com',
                    'port' => '',
                    'user' => '',
                    'pass' => '',
                    'path' => '/',
                    'query' => '',
                    'fragment' => 'nav'
                ],
            ],
            [
                'str' => 'https://max-power:1234abcd@92.16.33.40:443?id=5',
                'arr' => [
                    'scheme' => 'https',
                    'host' => '92.16.33.40',
                    'port' => '443',
                    'user' => 'max-power',
                    'pass' => '1234abcd',
                    'path' => '',
                    'query' => 'id=5',
                    'fragment' => ''
                ],
            ],
        ];
        foreach ($urls as $data) {
            $url = new \stdClass();
            foreach ($data['arr'] as $k => $v) {
                $url->$k = $v;
            }
            $this->assertSame($data['str'], URL::build($url));
        }
    }

    public function testEncode(): void
    {
        $string = 'abcd1234';
        $this->assertSame($string, URL::encode($string));

        $string = 'user@example.com';
        $this->assertSame($string, URL::encode($string));

        $this->assertSame(
            'https://test.com/?name[]=%D0%9C%D0%B0%D0%BA%D1%81%20%D0%90%D0%BD%D1%82%D0%B8%D0%BF%D0%B8%D0%BD',
            URL::encode('https://test.com/?name[]=Макс Антипин')
        );
    }

    public function testConstruct(): void
    {
        $url = new URL('https://example.com/');
        $this->assertSame('https', $url->scheme);
        $this->assertSame('example.com', $url->host);
        $this->assertSame('/', $url->path);
        $this->assertSame('', $url->query);
        $url = new URL(parse_url('mailto:ceo@maxiesystems.com'));
        $this->assertSame('mailto', $url->scheme);
        $this->assertSame('', $url->user);
        $this->assertSame('', $url->host);
        $this->assertSame('ceo@maxiesystems.com', $url->path);
        $u = 'https://service.ru/catalog/komplektuyushchie_dlya_remonta/zapchasti_dlya_apple/?per_page=100&PAGEN_1=2';
        $url = new URL(URL::Parse($u));
        $this->assertSame('https', $url->scheme);
        $this->assertSame('service.ru', $url->host);
        $this->assertSame('/catalog/komplektuyushchie_dlya_remonta/zapchasti_dlya_apple/', $url->path);
        $this->assertSame('per_page=100&PAGEN_1=2', $url->query);
    }

    public function testClone(): void
    {
        $url = new URL('https://example.com/');
        $url->query = new URL\Query(['test' => '1']);
        $new_url = clone $url;
        $new_url->query->test = '0';
        $this->assertSame('1', $url->query->test);
        $this->assertSame('0', $new_url->query->test);
    }

    public function testForeach(): void
    {
        $u = 'https://example.com:8080/pictures/search.php?size=actual&nocompress#main-nav';
        $url = new URL($u);
        $arr = $url->toArray();
        foreach ($url as $k => $v) {
            $this->assertSame($arr[$k], $url->$k);
        }
    }

    public function testParse(): void
    {
        $url = URL::parse('https://example.com/');
        $this->assertSame('https', $url->scheme);
        $this->assertSame('example.com', $url->host);
        $this->assertSame('/', $url->path);
        $this->assertSame('', $url->query);
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
            $url = new URL($s);
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
            $url = new URL($s);
            $this->assertSame($expected, $url->getType());
        }
    }

    public function testAddQueryParameters(): void
    {
        foreach (
            [
            ['https://example.com/?a=1&bb=22', 'https://example.com/', 'a=1&bb=22'],
            ['https://example.com/?a=1&bb=22', 'https://example.com/', ['a' => 1, 'bb' => 22]],
            ['https://example.com/?a=1&bb=22', 'https://example.com/?', 'a=1&bb=22'],
            ['https://example.com/?a=1&bb=22', 'https://example.com/?', ['a' => 1, 'bb' => 22]],
            ['https://test.com/?xyz=0&a=1&bb=22', 'https://test.com/?xyz=0', 'a=1&bb=22'],
            ['https://test.com/?xyz=&a=1&bb=22', 'https://test.com/?xyz=', ['a' => 1, 'bb' => 22]],
            ] as list($expected, $url, $params)
        ) {
            $this->assertSame($expected, URL::addQueryParameters($url, $params));
        }
    }

    public function testConstructFilter(): void
    {
        $url_str = 'https://example.com:8080/pictures/search.php?size=actual&nocompress#main-nav';
        $fragment = 'section-5';
        $url = new URL($url_str, function (string $name, $value, \Closure $src_url, string $fragment = null) {
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
