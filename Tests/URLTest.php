<?php

declare(strict_types=1);

namespace MaxieSystems\Tests;

use MaxieSystems\ArrayAccessProxy;
use MaxieSystems\URL;
use MaxieSystems\URLReadOnly;
use MaxieSystems\URLType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(URL::class)]
#[UsesClass(ArrayAccessProxy::class)]
#[UsesClass(URLReadOnly::class)]
#[UsesClass(URL\Query::class)]
final class URLTest extends TestCase
{
    #[DataProvider('dataProviderBuild')]
    public function testBuild($url, string $expected): void
    {
        $this->assertSame($expected, URL::build($url));
    }

    public static function dataProviderBuild(): \Generator
    {
        yield 'Empty object' => [
            (object)[
                'scheme' => '',
                'host' => '',
                'port' => '',
                'user' => '',
                'pass' => '',
                'path' => '',
                'query' => '',
                'fragment' => ''
            ],
            '',
        ];
        yield 'URL object with fragment' => [
            (object)[
                'scheme' => 'https',
                'host' => 'example.com',
                'port' => '',
                'user' => '',
                'pass' => '',
                'path' => '/',
                'query' => '',
                'fragment' => 'nav'
            ],
            'https://example.com/#nav',
        ];
        yield 'URL object' => [
            [
                'scheme' => 'https',
                'host' => '92.16.33.40',
                'port' => '443',
                'user' => 'max-power',
                'pass' => '1234abcd',
                'path' => '',
                'query' => 'id=5',
                'fragment' => ''
            ],
            'https://max-power:1234abcd@92.16.33.40:443?id=5',
        ];
    }

    #[DataProvider('dataProviderEncode')]
    public function testEncode(string $expected, string $value): void
    {
        $this->assertSame($expected, URL::encode($value));
    }

    public static function dataProviderEncode(): \Generator
    {
        $v = 'abcd1234';
        yield [$v, $v];
        $v = 'user@example.com';
        yield [$v, $v];
        yield [
            'https://test.com/?name[]=%D0%9C%D0%B0%D0%BA%D1%81%20%D0%90%D0%BD%D1%82%D0%B8%D0%BF%D0%B8%D0%BD',
            'https://test.com/?name[]=Макс Антипин'
        ];
    }

    #[DataProvider('dataProviderConstruct')]
    public function testConstruct(
        mixed $srcUrl, string $scheme, string $user, string $host, string $path, string $query
    ): void {
        $url = new URL($srcUrl);
        $this->assertSame($scheme, $url->scheme);
        $this->assertSame($user, $url->user);
        $this->assertSame($host, $url->host);
        $this->assertSame($path, $url->path);
        $this->assertSame($query, $url->query);
    }

    public static function dataProviderConstruct(): \Generator
    {
        yield 'URL from string' => [
            'https://example.us/', 'https', '', 'example.us', '/', '',
        ];
        yield 'Email from array' => [
            parse_url('mailto:ceo@maxiesystems.com'), 'mailto', '', '', 'ceo@maxiesystems.com', '',
        ];
        yield 'URL from object' => [
            URL::Parse(
                'https://service.ru/catalog/komplektuyushchie_dlya_remonta/zapchasti_dlya_apple/?per_page=100&PAGEN_1=2'
            ),
            'https',
            '',
            'service.ru',
            '/catalog/komplektuyushchie_dlya_remonta/zapchasti_dlya_apple/',
            'per_page=100&PAGEN_1=2',
        ];
    }

    public function testClone(): void
    {
        $url = new URL('https://example-domain.shop/');
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
        $url = URL::parse('https://sexample.me/');
        $this->assertSame('https', $url->scheme);
        $this->assertSame('sexample.me', $url->host);
        $this->assertSame('', $url->port);
        $this->assertSame('/', $url->path);
        $this->assertSame('', $url->query);
        $this->assertSame('', $url->fragment);
        $url = URL::parse('#');
        $this->assertSame('', $url->scheme);
        $this->assertSame('', $url->host);
        $this->assertSame('', $url->port);
        $this->assertSame('', $url->path);
        $this->assertSame('', $url->query);
        $this->assertSame('', $url->fragment);
    }

    public function testIsAbsolute(): void
    {
        foreach (
            [
                'https://msse2.maxtheps.beget.tech/index.php',
                'mailto:max.v.antipin@gmail.com',
            ] as $s
        ) {
            $url = new URL($s);
            $this->assertTrue($url->isAbsolute());
        }
        foreach (
            [
                '/my-page.html',
                '//msse2.maxtheps.beget.tech/?path=classes/URL/IsAbsolute.php',
                'max.v.antipin@gmail.com',
                'images/05/12/2023/pic-15.gif',
                '',
            ] as $s
        ) {
            $url = new URL($s);
            $this->assertNotTrue($url->isAbsolute());
        }
    }

    public function testIsPathAbsolute(): void
    {
        foreach (
            [
                '/',
                '/test',
            ] as $path
        ) {
            $is = URL::isPathAbsolute($path);
            $this->assertTrue($is);
        }
        foreach (
            [
                '',
                'test/my/software',
            ] as $path
        ) {
            $is = URL::isPathAbsolute($path);
            $this->assertNotTrue($is);
        }
    }

    public function testIsPathRelative(): void
    {
        foreach (
            [
                ['', true],
                ['sitemap.xml', false],
            ] as list($path, $is_empty_expected)
        ) {
            $is = URL::isPathRelative($path, $is_empty);
            $this->assertTrue($is);
            $this->assertSame($is_empty_expected, $is_empty);
        }
        foreach (
            [
                '/',
                '/index.php',
            ] as $path
        ) {
            $is = URL::isPathRelative($path, $is_empty);
            $this->assertNotTrue($is);
            $this->assertNotTrue($is_empty);
        }
    }

    public function testIsPathRootless(): void
    {
        foreach (
            [
                'index.html',
            ] as $path
        ) {
            $is = URL::isPathRootless($path);
            $this->assertTrue($is);
        }
        foreach (
            [
                '',
                '/',
                '/index.php',
            ] as $path
        ) {
            $is = URL::isPathRootless($path);
            $this->assertNotTrue($is);
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
                '' => URLType::Empty,
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

    public function testDeleteQueryParameters(): void
    {
        $q = 'size=actual&nocompress&opt[]=a&opt[]=b&opt[]=c&';
        foreach (
            [
                [['nocomp', 'c'], 0, $q],
                [['size'], 1, 'nocompress&opt[]=a&opt[]=b&opt[]=c&'],
                [['nocompress', 'opt'], 4, 'size=actual&'],
                [['size', 'nocompress', 'opt'], 5, ''],
            ] as list($params, $count, $query)
        ) {
            /** @var int $c */
            $this->assertSame($query, URL::deleteQueryParameters($q, $params, $c));
            $this->assertSame($count, $c);
        }
    }

    public function testArrayAccess(): void
    {
        $url_str = 'https://example.com:8080/pictures/search.php?max=5';
        $url = new URL($url_str, function (string $name, $value, array|\ArrayAccess $src_url) {
            return $value;
        });
        $this->assertSame('example.com', $url['host']);
        $this->assertNotTrue(isset($url['fake_property']));
        $this->assertTrue(isset($url['port']));
        $this->assertSame('max=5', $url['query']);
        unset($url['query']);
        $this->assertSame('', $url['query']);
    }

    public function testIsEmpty(): void
    {
        $url = new URL('https://example.com/');
        $this->assertNotTrue($url->isEmpty());
        $this->assertNotTrue($url->isEmpty('authority'));
        $url->scheme = $url->host = $url->path = '';
        $this->assertTrue($url->isEmpty());
        $this->assertTrue($url->isEmpty('authority'));
        $url = new URL('');
        $this->assertTrue($url->isEmpty());
        $this->assertTrue($url->isEmpty('authority'));
        $this->expectException(\UnexpectedValueException::class);
        $url->isEmpty('netloc');
    }

    public function testCopy(): void
    {
        $source_url = new URLReadOnly('http://a/b/c/d;p?q#f');
        $url = new URL('https://example.com:8080/');
        $url->copy($source_url, 'path', 'query');
        $this->assertSame($source_url->path, $url->path);
        $this->assertSame($source_url->query, $url->query);
        $url->copy($source_url, 'authority');
        foreach (['host', 'port', 'user', 'pass'] as $name) {
            $this->assertSame($source_url->$name, $url->$name);
        }
        $url->copy($source_url, 'origin');
        foreach (['scheme', 'host', 'port'] as $name) {
            $this->assertSame($source_url->$name, $url->$name);
        }
        $url->copy($source_url, 'authority', 'host', 'fragment');
        foreach (['host', 'port', 'user', 'pass', 'fragment'] as $name) {
            $this->assertSame($source_url->$name, $url->$name);
        }
        $url->copy($source_url);
        $this->assertSame((string)$source_url, (string)$url);
        $this->expectException(\UnexpectedValueException::class);
        $url->copy($source_url, 'path-query');
    }

    public function testRemoveDotSegments(): void
    {
        $urls = [
            '' => '',
            '/' => '/',
            '//' => '/',
            '///' => '/',
            '/test//' => '/test/',
            '//test/' => '/test/',
            '/test' => '/test',
            '/test/' => '/test/',
            'test/' => 'test/',
            '/a/b/c/./../../g' => '/a/g',
            'mid/content=5/../6' => 'mid/6',
            '/xxx/yyy/zzz/../../././././css/../main.css' => '/xxx/main.css',
            '../../../g'    =>  'g',
            '../../../../g' =>  'g',
            '/../../../g'    =>  '/g',
            '/../../../../g' =>  '/g',
            '.' => '',
            '..' => '',
        ];
        foreach ($urls as $url => $expected) {
            $this->assertSame($expected, URL::removeDotSegments($url));
        }
        $this->assertSame('', URL::removeDotSegments([]));
        $this->assertSame('', URL::removeDotSegments(['']));
        $this->assertSame('-', URL::removeDotSegments(['-']));
    }

    public function testMergePaths(): void
    {
        foreach ($this->getPaths() as list($base, $path, $expected_path_type, $expected_path)) {
            $this->assertSame($expected_path, URL::mergePaths($base, $path, $path_type));
            $this->assertSame($expected_path_type, $path_type);
        }
    }

    public function testPathToAbsolute(): void
    {
        foreach ($this->getPaths() as list($base, $path, , , $expected)) {
            $this->assertSame($expected, URL::pathToAbsolute($base, $path));
        }
    }

    private static function getPaths(): array
    {
        return [
            ['', '', URL\PathType::Empty, '', '/'],
            ['/', '', URL\PathType::Empty, '/', '/'],
            ['', '/', URL\PathType::Absolute, '/', '/'],
            ['/', '/', URL\PathType::Absolute, '/', '/'],
            ['', '././././css/main.css', URL\PathType::Rootless, '././././css/main.css', '/css/main.css'],
            [
                '/test',
                '././././css/main.css',
                URL\PathType::Rootless,
                '/././././css/main.css',
                '/css/main.css'
            ],
            [
                '/test/',
                '././././css/main.css',
                URL\PathType::Rootless,
                '/test/././././css/main.css',
                '/test/css/main.css'
            ],
            [
                '/root//user-1/.//my-page.html',
                '/css/../test55xx.css',
                URL\PathType::Absolute,
                '/css/../test55xx.css',
                '/test55xx.css',
            ],
            [
                '/root//user-1/.//my-page.html',
                'css/../test55xx.css',
                URL\PathType::Rootless,
                '/root//user-1/.//css/../test55xx.css',
                '/root/user-1/test55xx.css',
            ],
            [
                '/root//user-1/.//my-page.html',
                'css/../../test55xx.css',
                URL\PathType::Rootless,
                '/root//user-1/.//css/../../test55xx.css',
                '/root/test55xx.css',
            ],
            [
                '/root//user-1/.//my-page.html',
                'css/../../../test55xx.css',
                URL\PathType::Rootless,
                '/root//user-1/.//css/../../../test55xx.css',
                '/test55xx.css',
            ],
            [
                '/root//user-1/.//my-page.html',
                '../css/test55xx.css',
                URL\PathType::Rootless,
                '/root//user-1/.//../css/test55xx.css',
                '/root/css/test55xx.css',
            ],
            [
                '/root//user-1/.//my-page.html',
                '/../css/test55xx.css',
                URL\PathType::Absolute,
                '/../css/test55xx.css',
                '/css/test55xx.css',
            ],
        ];
    }

    public function testDebugInfo(): void
    {
        $url = new URL('https://example.com/');
        $info = $url->__debugInfo();
        $this->assertArrayHasKey('type', $info);
        $this->assertSame(URLType::Absolute, $info['type']);
    }
}
