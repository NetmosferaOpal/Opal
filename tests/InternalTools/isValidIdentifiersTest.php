<?php declare(strict_types = 1); // atom

namespace Netmosfera\OpalTests\InternalTools;

use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\InternalTools\isValidIdentifiers;

class isValidIdentifiersTest extends TestCase
{
    function data1(){
        yield [["Foo"], TRUE];
        yield [["Qux", "Foo", "Bar"], TRUE];

        yield [[], FALSE];
        yield [["1Bar"], FALSE];
        yield [["Foo", "1Bar"], FALSE];
        yield [["Qux", "Foo", "1Bar"], FALSE];
    }

    /** @dataProvider data1 */
    public function test1(array $identifiers, Bool $isValidIdentifiers){
        self::assertSame($isValidIdentifiers, isValidIdentifiers($identifiers));
    }
}
