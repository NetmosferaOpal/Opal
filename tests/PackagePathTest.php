<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackagePath;
use PHPUnit\Framework\TestCase;

class PackagePathTest extends TestCase
{
    public function data(){
        foreach(["\\", "/"] as $s){
            yield ["c:", "c:", strlen("c:")];
            yield ["c:{$s}bar", "c:{$s}bar", strlen("c:{$s}bar")];
            yield ["c:{$s}bar{$s}baz", "c:{$s}bar{$s}baz", strlen("c:{$s}bar{$s}baz")];
            yield ["", "", strlen("")];
            yield ["{$s}bar", "{$s}bar", strlen("{$s}bar")];
            yield ["{$s}bar{$s}baz", "{$s}bar{$s}baz", strlen("{$s}bar{$s}baz")];
        }
    }

    /** @dataProvider data */
    public function test(String $input, String $expect, Int $length){
        $package = new Package("StarkIndustries", "MarkLI");
        $d = new PackagePath($package, $input);
        self::assertSame($package, $d->package);
        self::assertSame($expect, $d->path);
        self::assertSame($length, $d->pathLength);
    }
}
