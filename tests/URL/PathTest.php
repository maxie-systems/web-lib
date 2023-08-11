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
                '/' => ['/' => ''],
                '/my-project' => [
//                    '/my-project' => '',
                ],
                '/my-project/' => [
                    '/' => '/my-project',
//                    '/my-project' => [false],// или здесь true?
                ],
                '/my-project/tests' => [
//                    '/tests' => '/my-project',
//                    'tests' => '/my-project/',
                ],
            ] as $p => $test
        ) {
            $path = new Path($p);
            foreach ($test as $value => $expected) {
                $this->assertTrue($path->endsWith($value, $sub));
//                $this->assertSame($expected, $sub);
            }
        }
        foreach (
            [
                '' => ['/'],
                '/my-project' => [
                    '/',
                ],
                '/my-project/' => [
//                    '/my-project' => [false],// или здесь true?
                ],
                '/my-project' => [
                    '/my-project/',
                ],
                '/my-project/tests' => [
                    '/my-project',
                    'ests',
                ],
            ] as $p => $test
        ) {
            $path = new Path($p);
            foreach ($test as $value) {
                $this->assertNotTrue($path->endsWith($value, $sub));
                $this->assertNull($sub);
            }
        }
    }

    public function testStartsWith(): void
    {
        foreach (
            [
                '/' => ['/' => ''],
                '/my-project' => [
                    '/' => 'my-project',
//                    '/my-project' => '',
                ],
                '/my-project/' => [
                    '/' => 'my-project/',
//                    '/my-project' => '/',
                ],
                '/my-project/tests' => [
                    '/' => 'my-project/tests',
  //                  '/my-project' => '/tests',
//                    'my-project' => [true],// или здесь false?
                ],
            ] as $p => $test
        ) {
            $path = new Path($p);
            foreach ($test as $value => $expected) {
                $this->assertTrue($path->startsWith($value, $sub));
//                $this->assertSame($expected, $sub);
            }
        }
        foreach (
            [
                '' => ['/'],
                '/my-project' => [
                    '/my-project/',
                ],
                '/my-project/tests' => [
                    '/tests',
//                    'my-project' => [true],// или здесь false?
                    '/my-proj',
                ],
            ] as $p => $test
        ) {
            $path = new Path($p);
            foreach ($test as $value) {
                $this->assertNotTrue($path->startsWith($value, $sub));
                $this->assertNull($sub);
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
