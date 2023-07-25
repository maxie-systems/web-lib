<?php

declare(strict_types=1);

namespace MaxieSystems\URL\Path;

use PHPUnit\Framework\TestCase;

final class SegmentsTest extends TestCase
{
    public function testFilterSegment(): void
    {
        $filter = function (string $segment, int $i, int $last_i): ?string {
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
                $segments = new Segments($path, [Segments::class, 'filterSegmentRaw']);
                $this->assertSame($expected, (string)$segments);
            }
        }
    }

    public function testOffsetGet(): void
    {
        $segments = new Segments('/my/pathname/index.php');
        $this->assertSame('pathname', $segments[1]);
    }

    public function testOffsetExists(): void
    {
        $segments = new Segments('/my/pathname/index.php');
        $this->assertTrue(isset($segments[0]));
        $this->assertNotTrue(isset($segments[10]));
        $this->assertTrue(isset($segments[-1]));
        $this->assertNotTrue(isset($segments[-11]));
    }

    public function testOffsetSet(): void
    {
        $segments = new Segments('/my/pathname/index.php');
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
        $tests = [
            'test/page/my-xxx.html/abc' => [false, null],
            'test/page/not-my' => [false, null],
            'test/page' => [true, 'my-xxx.html'],
            '/test/page' => [true, 'my-xxx.html'],
            '/test/page/' => [true, 'my-xxx.html'],
            'test//page' => [true, 'my-xxx.html'],
            '/test//page' => [true, 'my-xxx.html'],
            '/test//page/' => [true, 'my-xxx.html'],
            'test/page/my-xxx.html' => [true, ''],
            '/test/page/my-xxx.html' => [true, ''],
        ];
        $s = new Segments('test/page/my-xxx.html');
        foreach ($tests as $path => $expected) {
            $this->assertSame($expected[0], $s->startsWith($path, $sub));
            $this->assertSame($expected[1], $sub);
        }
    }
}
