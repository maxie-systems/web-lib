<?php

declare(strict_types=1);

namespace MaxieSystems\URL;

use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase
{
    public function testEndsWith(): void
    {
        foreach (
            [
                '' => ['/' => [false]],
                '/' => ['/' => [true]],
                '/my-project' => [
                    '/' => [false],
                    '/my-project' => [true],
                ],
                '/my-project/' => [
                    '/' => [true],
                    '/my-project' => [false],// или здесь true?
                ],
                '/my-project' => [
                    '/my-project/' => [false],
                ],
                '/my-project/tests' => [
                    '/' => [true],
                    '/my-project' => [true],
                    '/tests' => [true],
                    'tests' => [true],
                    'ests' => [false],
                ],
            ] as $p => $test
        ) {
            $path = new Path($p);
            foreach ($test as $value => list($expected)) {
                $res = $path->endsWith($value);
                if ($expected) {
                    $this->assertTrue($res);
                } else {
                    $this->assertNotTrue($res);
                }
            }
        }
    }

    public function testStartsWith(): void
    {
        foreach (
            [
                '' => ['/' => [false]],
                '/' => ['/' => [true]],
                '/my-project' => [
                    '/' => [true],
                    '/my-project' => [true],
                ],
                '/my-project/' => [
                    '/' => [true],
                    '/my-project' => [true],
                ],
                '/my-project' => [
                    '/my-project/' => [false],
                ],
                '/my-project/tests' => [
                    '/' => [true],
                    '/my-project' => [true],
                    '/tests' => [false],
                    'my-project' => [true],// или здесь false?
                    '/my-proj' => [false],
                ],
            ] as $p => $test
        ) {
            $path = new Path($p);
            foreach ($test as $value => list($expected)) {
                $res = $path->startsWith($value);
                if ($expected) {
                    $this->assertTrue($res);
                } else {
                    $this->assertNotTrue($res);
                }
            }
        }
    }

    public function testToString(): void
    {
        foreach (
            [
                '/test/page/my-xxx.html',
                'test/page/my-xxx.html',
                '',
                '/',
                '/docker/',
                '/docker',
            ] as $p
        ) {
            $path = new Path($p);
            $this->assertSame($p, (string)$path);
        }
        foreach (
            [
                '/test//page/my-xxx.html' => '/test/page/my-xxx.html',
                'test///page//my-xxx.html' => 'test/page/my-xxx.html',
                '//docker' => '/docker',
                '//' => '/',
                '/docker//' => '/docker/',
            ] as $p => $expected
        ) {
            $path = new Path($p);
            $this->assertSame($expected, (string)$path);
        }
    }
}
