<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;
use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\InternalTools\componentFromTypeName;

class componentFromTypeNameTest extends TestCase
{
    function data1(){
        yield ["Foo"];
        yield ["Foo\\Bar"];
        yield ["Foo//Bar"];
    }

    /** @dataProvider data1 */
    public function test1(String $typeName){
        // test that returns NULL if not in the format vendor\package\*
        self::assertSame(NULL, componentFromTypeName($typeName));
    }

    function data2(){
        $package = new Package("Foo", "Bar");

        yield [
            "Foo\\Bar\\Baz",
            new PackageComponent($package, ["Baz"], ".php")
        ];

        yield [
            "Foo\\Bar\\Baz\Qux",
            new PackageComponent($package, ["Baz", "Qux"], ".php")
        ];

        yield [
            "Foo\\Bar\\Baz\Qux\\Tux",
            new PackageComponent($package, ["Baz", "Qux", "Tux"], ".php")
        ];
    }

    /** @dataProvider data2 */
    public function test2(String $typeName, PackageComponent $expected){
        self::assertEquals($expected, componentFromTypeName($typeName));
    }
}
