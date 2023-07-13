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

    /*public function testCannotBeCreatedFromInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Email::fromString('invalid');
    }*/
}
