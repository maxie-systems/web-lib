<?php

declare(strict_types=1);

namespace MaxieSystems\URL;

use MaxieSystems\URL\Exception\InvalidHostException;
use MaxieSystems\URL\Exception\InvalidSchemeException;
use MaxieSystems\URLReadOnly;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Resolver::class)]
#[UsesClass(\MaxieSystems\URL::class)]
#[UsesClass(URLReadOnly::class)]
final class ResolverTest extends TestCase
{
    public function testInvoke(): void
    {
        $base_url = new URLReadOnly('http://a/b/c/d;p?q#f');
        $resolver = new Resolver($base_url);
        foreach ($this->getURLs() as $u => $expected) {
            $url = new URLReadOnly($u, $resolver);
            $this->assertSame($expected, (string)$url);
        }
    }

    public function testResolve(): void
    {
        $base_url = new URLReadOnly('http://a/b/c/d;p?q#f');
        $resolver = new Resolver($base_url);
        foreach ($this->getURLs() as $u => $expected) {
            $url = new \MaxieSystems\URL($u);
            $resolver($url);
            $this->assertSame($expected, (string)$url);
        }
    }

    public function testNoScheme(): void
    {
        $base_url = new URLReadOnly('//example.com/');
        $this->expectException(InvalidSchemeException::class);
        $this->expectExceptionMessageMatches('/\bscheme undefined\b/');
        new Resolver($base_url);
    }

    public function testNoHost(): void
    {
        $base_url = new URLReadOnly(['scheme' => 'https']);
        $this->expectException(InvalidHostException::class);
        $this->expectExceptionMessageMatches('/\bhost undefined\b/');
        new Resolver($base_url);
    }

    public static function getURLs(): array
    {
        # Normal Examples (https://datatracker.ietf.org/doc/html/rfc3986#section-5.4.1)
        $urls = [
            'g:h'        => 'g:h',
            'g'          => 'http://a/b/c/g',
            './g'        => 'http://a/b/c/g',
            'g/'         => 'http://a/b/c/g/',
            '/g'         => 'http://a/g',
            '//g'        => 'http://g',
            '?y'         => 'http://a/b/c/d;p?y',
            'g?y'        => 'http://a/b/c/g?y',
            'g?y/./x'    => 'http://a/b/c/g?y/./x',
            '#s'         => 'http://a/b/c/d;p?q#s',
            'g#s'        => 'http://a/b/c/g#s',
            'g#s/./x'    => 'http://a/b/c/g#s/./x',
            'g?y#s'      => 'http://a/b/c/g?y#s',
            ';x'         => 'http://a/b/c/;x',
            'g;x'        => 'http://a/b/c/g;x',
            'g;x?y#s'    => 'http://a/b/c/g;x?y#s',
            '.'          => 'http://a/b/c/',
            './'         => 'http://a/b/c/',
            '..'         => 'http://a/b/',
            '../'        => 'http://a/b/',
            '../g'       => 'http://a/b/g',
            '../..'      => 'http://a/',
            '../../'     => 'http://a/',
            '../../g'    => 'http://a/g',
        ];
        # Abnormal Examples (https://datatracker.ietf.org/doc/html/rfc3986#section-5.4.2)
        $urls += [
            # An empty reference resolves to the complete base URL:
            ''            => 'http://a/b/c/d;p?q#f',
            '../../../g'    =>  'http://a/g',
            '../../../../g' =>  'http://a/g',
/*    Similarly, parsers must avoid treating "." and ".." as special when
            they are not complete components of a relative path.

            "/./g"          =  "http://a/g"
            "/../g"         =  "http://a/g"
            "g."            =  "http://a/b/c/g."
            ".g"            =  "http://a/b/c/.g"
            "g.."           =  "http://a/b/c/g.."
            "..g"           =  "http://a/b/c/..g"

            Less likely are cases where the relative URL uses unnecessary or
            nonsensical forms of the "." and ".." complete path segments.

            "./../g"        =  "http://a/b/g"
            "./g/."         =  "http://a/b/c/g/"
            "g/./h"         =  "http://a/b/c/g/h"
            "g/../h"        =  "http://a/b/c/h"
            "g;x=1/./y"     =  "http://a/b/c/g;x=1/y"
            "g;x=1/../y"    =  "http://a/b/c/y"

            "g?y/./x"       =  "http://a/b/c/g?y/./x"
            "g?y/../x"      =  "http://a/b/c/g?y/../x"
            "g#s/./x"       =  "http://a/b/c/g#s/./x"
            "g#s/../x"      =  "http://a/b/c/g#s/../x"*/
        ];
        return $urls;
    }
}
