<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageDirectory;
use PHPUnit\Framework\TestCase;

class PackageDirectoryTest extends TestCase
{
    public function data(){
        yield ["c:\\bar\\baz\\", "c:\\bar\\baz", strlen("c:\\bar\\baz")];
        yield ["c:\\bar\\baz\\\\", "c:\\bar\\baz", strlen("c:\\bar\\baz")];
        yield ["c:\\bar\\baz\\\\\\", "c:\\bar\\baz", strlen("c:\\bar\\baz")];

        yield ["c:/bar/baz/", "c:/bar/baz", strlen("c:/bar/baz")];
        yield ["c:/bar/baz//", "c:/bar/baz", strlen("c:/bar/baz")];
        yield ["c:/bar/baz///", "c:/bar/baz", strlen("c:/bar/baz")];

        yield ["/bar/baz/", "/bar/baz", strlen("/bar/baz")];
        yield ["/bar/baz//", "/bar/baz", strlen("/bar/baz")];
        yield ["/bar/baz///", "/bar/baz", strlen("/bar/baz")];

        yield ["/", "", 0];
        yield ["//", "", 0];
        yield ["///", "", 0];
    }

    /** @dataProvider data */
    public function test(String $input, String $expect, Int $length){
        $package = new Package("StarkIndustries", "MarkLI");
        $d = new PackageDirectory($package, $input);
        self::assertSame($package, $d->package);
        self::assertSame($expect, $d->path);
        self::assertSame($length, $d->pathLength);
    }
}
