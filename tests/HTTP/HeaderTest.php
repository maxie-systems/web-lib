<?php

declare(strict_types=1);

namespace MaxieSystems\HTTP;

use MaxieSystems\Exception\HTTP\EmptyHeaderNameException;
use PHPUnit\Framework\TestCase;

final class HeaderTest extends TestCase
{
    public function testConstruct(): void
    {
        foreach (
            [
                [['Expires: Thu, 05 Oct 2023 18:01:30 GMT'], 'Expires'],
                [['Content-Type', 'text/html; charset=UTF-8'], 'Content-Type'],
                [['content-type: application/x-javascript'], 'Content-Type'],
                [['ETag: "33a64df551425fcc55e4d42a148795d9f25f89d4"'], 'ETag'],
            ] as list($args, $name)
        ) {
            $header = new Header(...$args);
            $this->assertSame($name, $header->name);
            $this->assertSame(strtolower($name), $header->name_lc);
        }
    }

    public function testEmptyHeaderName(): void
    {
        $this->expectException(EmptyHeaderNameException::class);
        $header = new Header('HTTP/1.1 404 Not Found');
        $header->value;
    }

    public function testToString(): void
    {
        $value = 'application/x-javascript';
        $header = new Header('content-type', $value);
        $this->assertSame("Content-Type: $value", (string)$header);
    }

    public function testDebugInfo(): void
    {
        $header = new Header('Content-Encoding', 'gzip');
        $info = $header->__debugInfo();
        $this->assertArrayHasKey('header', $info);
        $this->assertSame('Content-Encoding: gzip', $info['header']);
    }
}
