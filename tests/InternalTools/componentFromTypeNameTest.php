<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\InternalTools\componentFromTypeName;

class componentFromTypeNameTest extends TestCase
{
    public function data1(){
        yield [""];
        yield ["Foo"];
        yield ["Foo\\Bar"];
    }

    /** @dataProvider data1 */
    public function test1(String $typeName){
        self::assertSame(NULL, componentFromTypeName($typeName));
    }

    public function test_object(){
        $actual = componentFromTypeName("Foo\\Bar\\Baz");
        self::assertEquals("/Foo/Bar/Baz.php", $actual->absolutePath);
        self::assertEquals("/Baz.php", $actual->relativeToPackagePath);
        self::assertEquals("/Baz", $actual->name->path());
        self::assertEquals(".php", $actual->extensions);
        self::assertEquals("Foo", $actual->package->vendor->string);
        self::assertEquals("Bar", $actual->package->name->string);
    }
}
