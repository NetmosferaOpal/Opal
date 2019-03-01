<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Identifier;
use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackagePath;
use Netmosfera\Opal\Path;
use PHPUnit\Framework\TestCase;

class PackagePathTest extends TestCase
{
    public function test(){
        $package = new Package(new Identifier("A"), new Identifier("B"));
        $path = new Path("/foo/bar");
        $packagePath = new PackagePath($package, $path);

        self::assertSame($package, $packagePath->package);
        self::assertSame($path, $packagePath->path);
    }
}
