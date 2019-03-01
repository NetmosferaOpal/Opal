<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Path;
use Netmosfera\Opal\PathException;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function data_construct(){
        foreach(["\\", "/"] as $s){
            foreach(["c:", "D:", ""] as $p){
                yield ["{$p}",                  strlen("{$p}")];
                yield ["{$p}{$s}bàr",           strlen("{$p}{$s}bàr")];
                yield ["{$p}{$s}bàr{$s}bàz",    strlen("{$p}{$s}bàr{$s}bàz")];
            }
        }
    }

    /** @dataProvider data_construct */
    public function test_construct(String $string, Int $length){
        $path = new Path($string);
        self::assertSame($string, $path->string);
        self::assertSame($length, $path->length);
    }

    public function data_failure(){
        foreach(["\\", "/"] as $s){
            foreach(["c:", "D:", ""] as $p){
                yield ["{$p}{$s}"];
                yield ["{$p}{$s}bàr{$s}"];
                yield ["{$p}{$s}bàr{$s}bàz{$s}"];
            }
        }
    }

    /** @dataProvider data_failure */
    public function test_failure(String $string){
        $this->expectException(PathException::CLASS);
        new Path($string);
    }

    public function data_is_in(){
        foreach(["/", "\\"] as $s){
            foreach(["c:", "D:", ""] as $p){
                yield ["{$p}",                      "{$p}{$s}A", TRUE];
                yield ["{$p}{$s}A",                 "{$p}{$s}A{$s}B", TRUE];
                yield ["{$p}{$s}A{$s}B",            "{$p}{$s}A{$s}B{$s}C", TRUE];

                yield ["{$p}",                      "{$p}{$s}A{$s}B", TRUE];
                yield ["{$p}{$s}A",                 "{$p}{$s}A{$s}B{$s}C", TRUE];
                yield ["{$p}{$s}A{$s}B",            "{$p}{$s}A{$s}B{$s}C{$s}D", TRUE];

                yield ["{$p}",                      "{$p}{$s}A{$s}B{$s}C", TRUE];
                yield ["{$p}{$s}A",                 "{$p}{$s}A{$s}B{$s}C{$s}D", TRUE];
                yield ["{$p}{$s}A{$s}B",            "{$p}{$s}A{$s}B{$s}C{$s}D{$s}E", TRUE];

                yield ["{$p}",                      "{$p}", FALSE];
                yield ["{$p}{$s}A",                 "{$p}{$s}A", FALSE];
                yield ["{$p}{$s}A{$s}B",            "{$p}{$s}A{$s}B", FALSE];
                yield ["{$p}{$s}A{$s}B{$s}C",       "{$p}{$s}A{$s}B{$s}C", FALSE];

                yield ["{$p}{$s}dir",               "{$p}{$s}directory", FALSE];
                yield ["{$p}{$s}A{$s}dir",          "{$p}{$s}A{$s}directory", FALSE];
                yield ["{$p}{$s}A{$s}B{$s}dir",     "{$p}{$s}A{$s}B{$s}directory", FALSE];
            }
        }
    }

    /** @dataProvider data_is_in */
    public function test_is_in(String $parentString, String $childString, Bool $isIn){
        $parent = new Path($parentString);
        $child = new Path($childString);
        self::assertSame($isIn, $child->isIn($parent));
    }
}
