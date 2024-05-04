<?php

declare(strict_types=1);

namespace MaxieSystems\Tests\URL\Path;

use MaxieSystems\EMessages;
use MaxieSystems\URL\Path\Segments;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Segments::class)]
#[UsesClass(EMessages::class)]
final class SegmentsTest extends TestCase
{
    public static function sliceDataProvider(): array
    {
        return [
            [[2], 'to/file2'],
            [[0, 2], 'my/path'],
            [[2, -3], ''],
            [[-2], 'to/file2'],
            [[-3, -1], 'path/to'],
        ];
    }

    #[DataProvider('sliceDataProvider')]
    public function testSlice(array $args, string $expected): void
    {
        $path = new Segments('my/path/to/file2');
        $new_path = $path->slice(...$args);
        $this->assertInstanceOf(Segments::class, $new_path);
        $this->assertSame($expected, (string)$new_path);
    }

    public static function splitDataProvider(): array
    {
        return [
            [[2], 'my/path', 'to/file'],
            [[-3], 'my', 'path/to/file'],
            [[-3, true], 'my', 'to/file'],
        ];
    }

    #[DataProvider('splitDataProvider')]
    public function testSplit(array $args, string $expected0, string $expected1): void
    {
        $path = new Segments('my/path/to/file');
        $new_path = $path->split(...$args);
        $this->assertInstanceOf(Segments::class, $new_path[0]);
        $this->assertInstanceOf(Segments::class, $new_path[1]);
        $this->assertSame($expected0, (string)$new_path[0]);
        $this->assertSame($expected1, (string)$new_path[1]);
    }

    public function testFilterSegment(): void
    {
        $filter = function (string $segment): ?string {
            if ('' === $segment) {
                return null;
            }
            return strtolower($segment);
        };
        $segments = new Segments('/My/CaseSensitive/PathName/index.php', $filter);
        $this->assertSame('pathname', $segments[2]);
    }

    public function testFilterSegmentRaw(): void
    {
        $tests = [
            'test/page' => [],
            'test//page' => [],
        ];
        foreach ($tests as $path => $values) {
            $tests[$path] = [$path, "/$path", "$path/"];
        }
        foreach ($tests as $expected => $values) {
            foreach ($values as $path) {
                $segments = new Segments($path, null, Segments::FILTER_RAW);
                $this->assertSame($expected, (string)$segments);
            }
        }
        $segments = new Segments('');
        $this->assertCount(0, $segments);
        $segments = new Segments('', null, Segments::FILTER_RAW);
        $this->assertCount(0, $segments);
        $segments = new Segments('/');
        $this->assertCount(0, $segments);
        $segments = new Segments('/', null, Segments::FILTER_RAW);
        $this->assertCount(0, $segments);
        $segments = new Segments('/', function (string $segment): string {
            return $segment;
        });
        $this->assertCount(2, $segments);
    }

    public function testNoFilterSegment(): void
    {
        $segments = new Segments(['folder', null, null, 'another-folder'], false);
        $this->assertCount(2, $segments);
    }

    public function testOffsetGet(): void
    {
        $segments = new Segments('/first/pathname/index.php');
        $this->assertSame('pathname', $segments[1]);
    }

    public function testOffsetExists(): void
    {
        $segments = new Segments('/second/pathname/index.php');
        $this->assertTrue(isset($segments[0]));
        $this->assertNotTrue(isset($segments[10]));
        $this->assertTrue(isset($segments[-1]));
        $this->assertNotTrue(isset($segments[-11]));
    }

    public function testOffsetSet(): void
    {
        $segments = new Segments('/third/pathname/index.php');
        $this->expectException(\Error::class);
        $segments[0] = 'xxx';
    }

    public function testOffsetUnset(): void
    {
        $segments = new Segments('/my/pathname/index.php');
        $this->expectException(\Error::class);
        unset($segments[0]);
    }

    public function testStartsWith(): void
    {
        $slug = 'my-xxx.html';
        $path = 'test/page/' . $slug;
        $s = new Segments($path);
        $tests = [
            'test' => 'page/' . $slug,
            'test/page' => $slug,
            '/test/page' => $slug,
            '/test/page/' => $slug,
            'test//page' => $slug,
            '/test//page' => $slug,
            '/test//page/' => $slug,
            $path => '',
            '/test/page/my-xxx.html' => '',
        ];
        foreach ($tests as $path => $expected) {
            $sub = '';
            $this->assertTrue($s->startsWith($path, $sub));
            $this->assertSame($expected, $sub);
            $sub = [];
            $this->assertTrue($s->startsWith($path, $sub));
            $this->assertSame($expected ? explode('/', $expected) : [], $sub);
            $sub = null;
            $this->assertTrue($s->startsWith($path, $sub));
            $this->assertInstanceOf($s::class, $sub);
            $this->assertSame($expected, (string)$sub);
        }
        $tests = [
            'test/page/my-xxx.html/abc',
            'test/page/not-my',
            'test/pa',
        ];
        foreach ($tests as $path) {
            $sub = '';
            $this->assertFalse($s->startsWith($path, $sub));
            $this->assertNull($sub);
            $sub = [];
            $this->assertFalse($s->startsWith($path, $sub));
            $this->assertNull($sub);
        }
    }
}
