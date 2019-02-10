<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools\File;

use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\InternalTools\File\isAbsolutePath;

class isAbsolutePathTest extends TestCase
{
    function data1(){
        yield ["c:\\", TRUE];
        yield ["c:/", TRUE];
        yield ["C:\\", TRUE];
        yield ["C:/", TRUE];
        yield ["c:\\foo\\bar", TRUE];
        yield ["c:/foo/bar", TRUE];
        yield ["C:\\foo\\bar", TRUE];
        yield ["C:/foo/bar", TRUE];

        yield ["/", TRUE];
        yield ["/foo", TRUE];
        yield ["/foo/bar", TRUE];
        yield ["/foo/bar/baz", TRUE];

        yield ["", FALSE];
        yield ["foo", FALSE];

        yield ["foo/bar", FALSE];
        yield ["foo/bar/baz", FALSE];

        yield ["foo\\bar", FALSE];
        yield ["foo\\bar\\baz", FALSE];

        yield [".\\foo\\bar", FALSE];
        yield [".\\foo\\bar\\baz", FALSE];
        yield ["..\\foo\\bar", FALSE];
        yield ["..\\foo\\bar\\baz", FALSE];

        yield ["./foo/bar", FALSE];
        yield ["./foo/bar/baz", FALSE];
        yield ["../foo/bar", FALSE];
        yield ["../foo/bar/baz", FALSE];
    }

    /** @dataProvider data1 */
    public function test1(String $path, Bool $isAbsolute){
        self::assertSame($isAbsolute, isAbsolutePath($path));
    }
}
