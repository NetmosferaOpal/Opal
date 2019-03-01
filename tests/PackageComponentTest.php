<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Component;
use Netmosfera\Opal\Identifier;
use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;
use PHPUnit\Framework\TestCase;

class PackageComponentTest extends TestCase
{
    public function test(){
        $package = new Package(new Identifier("A"), new Identifier("B"));
        $componentName = new Component([new Identifier("C"), new Identifier("D")]);
        $c = new PackageComponent($package, $componentName, ".php");
        self::assertSame($package, $c->package);
        self::assertSame($componentName, $c->name);
        self::assertSame(".php", $c->extensions);
        self::assertSame("/C/D.php", $c->relativeToPackagePath);
        self::assertSame("/A/B/C/D.php", $c->absolutePath);
    }
}
