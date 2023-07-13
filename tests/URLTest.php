<?php

declare(strict_types=1);

namespace MaxieSystems;

use PHPUnit\Framework\TestCase;

final class URLTest extends TestCase
{
    public function testEncode(): void
    {
        $string = 'abcd1234';
        $this->assertSame($string, URL::encode($string));

        $string = 'user@example.com';
        $this->assertSame($string, URL::encode($string));
    }

    /*public function testCannotBeCreatedFromInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Email::fromString('invalid');
    }*/
}
