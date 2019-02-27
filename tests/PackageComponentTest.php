<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;
use PHPUnit\Framework\TestCase;

class PackageComponentTest extends TestCase
{
    public function test(){
        $package = new Package("StarkIndustries", "MarkLI");
        $identifiers = ["Weapons", "Minigun"];
        $c = new PackageComponent($package, $identifiers, ".php");
        self::assertSame($package, $c->package);
        self::assertSame($identifiers, $c->identifiers);
        self::assertSame(".php", $c->extensions);
        self::assertSame("/Weapons/Minigun.php", $c->relativeToPackagePath);
        self::assertSame("/StarkIndustries/MarkLI/Weapons/Minigun.php", $c->absolutePath);
    }
}
