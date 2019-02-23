<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\InternalTools\isNormalizedPath;

class isNormalizedPathTest extends TestCase
{
    public function data1(){
        foreach(["/", "\\"] as $s){

            yield ["c:", TRUE];
            yield ["c:{$s}foo", TRUE];
            yield ["c:{$s}foo{$s}bar", TRUE];
            yield ["c:{$s}foo{$s}bar{$s}baz", TRUE];

            yield ["", TRUE];

            yield ["{$s}foo", TRUE];
            yield ["{$s}foo{$s}bar", TRUE];
            yield ["{$s}foo{$s}bar{$s}baz", TRUE];

            yield ["foo", FALSE];
            yield ["foo{$s}bar", FALSE];
            yield ["foo{$s}bar{$s}baz", FALSE];

            yield ["{$s}", FALSE];
            yield ["{$s}foo{$s}", FALSE];
            yield ["{$s}foo{$s}bar{$s}", FALSE];
            yield ["{$s}foo{$s}bar{$s}baz{$s}", FALSE];

            yield [".", FALSE];
            yield [".{$s}foo", FALSE];
            yield [".{$s}foo{$s}bar", FALSE];
            yield [".{$s}foo{$s}bar{$s}baz", FALSE];

            yield ["..", FALSE];
            yield ["..{$s}foo", FALSE];
            yield ["..{$s}foo{$s}bar", FALSE];
            yield ["..{$s}foo{$s}bar{$s}baz", FALSE];
        }
    }

    /** @dataProvider data1 */
    public function test1(String $path, Bool $isAbsolute){
        self::assertSame($isAbsolute, isNormalizedPath($path));
    }
}
