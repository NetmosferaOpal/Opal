<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackagePath;
use Netmosfera\Opal\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function data(){
        foreach(["\\", "/"] as $s){
            yield ["c:",                strlen("c:")];
            yield ["c:{$s}bar",         strlen("c:{$s}bar")];
            yield ["c:{$s}bar{$s}baz",  strlen("c:{$s}bar{$s}baz")];
            yield ["",                  strlen("")];
            yield ["{$s}bar",           strlen("{$s}bar")];
            yield ["{$s}bar{$s}baz",    strlen("{$s}bar{$s}baz")];
        }
    }

    /** @dataProvider data */
    public function test(String $string, Int $length){
        $path = new Path($string);
        self::assertSame($string, $path->path);
        self::assertSame($length, $path->length);
    }
}
