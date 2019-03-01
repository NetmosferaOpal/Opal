<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Identifier;
use Netmosfera\Opal\Package;
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{
    public function test(){
        $vendorIdentifier = new Identifier("A");
        $nameIdentifier = new Identifier("B");
        $package = new Package($vendorIdentifier, $nameIdentifier);
        self::assertSame($vendorIdentifier, $package->vendor);
        self::assertSame($nameIdentifier, $package->name);
        self::assertSame("A;B", $package->id);
    }
}
