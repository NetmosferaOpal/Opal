<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\InternalTools\isValidIdentifier;

class isValidIdentifierTest extends TestCase
{
    function data1(){
        yield ["Foo", TRUE];
        yield ["1Bar", FALSE];
    }

    /** @dataProvider data1 */
    public function test1(String $path, Bool $isValidIdentifier){
        self::assertSame($isValidIdentifier, isValidIdentifier($path));
    }
}
