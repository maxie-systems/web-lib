<?php

declare(strict_types=1);

namespace MaxieSystems\Tests\HTTP;

use MaxieSystems\HTTP\Exception\EmptyHeaderNameException;
use MaxieSystems\HTTP\Header;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Header::class)]
final class HeaderTest extends TestCase
{
    public static function constructDataProvider(): array
    {
        return [
            [['Expires: Thu, 05 Oct 2023 18:01:30 GMT'], 'Expires'],
            [['Content-Type', 'text/html; charset=UTF-8'], 'Content-Type'],
            [['content-type: application/x-javascript'], 'Content-Type'],
            [['ETag: "33a64df551425fcc55e4d42a148795d9f25f89d4"'], 'ETag'],
        ];
    }

    #[DataProvider('constructDataProvider')]
    public function testConstruct(array $args, string $name): void
    {
        $header = new Header(...$args);
        $this->assertSame($name, $header->name);
        $this->assertSame(strtolower($name), $header->nameLC);
    }

    public function testEmptyHeaderName(): void
    {
        $this->expectException(EmptyHeaderNameException::class);
        echo new Header('HTTP/1.1 404 Not Found');
    }

    public function testEmptyHeader(): void
    {
        $this->expectException(EmptyHeaderNameException::class);
        echo new Header('', '');
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
