<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\Loaders;

use Netmosfera\Opal\Loaders\StaticLoader;
use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageDirectory;
use PHPUnit\Framework\TestCase;
use function ob_get_clean;
use function ob_start;

class StaticLoaderTest extends TestCase
{
    public function test(){
        try{
            $vendorName = "V" . bin2hex(random_bytes(5));
            $packageName = "P" . bin2hex(random_bytes(5));

            mkdir(__DIR__ . "/d/$vendorName/$packageName", 0777, TRUE);

            file_put_contents(__DIR__ . "/d/static-inclusions.php", "<?php echo 123;");

            file_put_contents(
                __DIR__ . "/d/$vendorName/$packageName/Foo.php",
                "<?php namespace $vendorName\\$packageName; class Foo{}"
            );

            $package = new Package($vendorName, $packageName);
            $directory = new PackageDirectory($package, "/irrelevant");
            $loader = new StaticLoader();

            ob_start();
            $loader->start([$package->id => $directory], [],  __DIR__ . "/d", 0777, 0777);
            self::assertSame("123", ob_get_clean());

            $class = "$vendorName\\$packageName\\Foo";
            self::assertInstanceOf($class, new $class);
        }finally{
            @unlink(__DIR__ . "/d/$vendorName/$packageName/Foo.php");
            @unlink(__DIR__ . "/d/static-inclusions.php");
            @rmdir(__DIR__ . "/d/$vendorName/$packageName");
            @rmdir(__DIR__ . "/d/$vendorName");
            @rmdir(__DIR__ . "/d");
        }
    }
}
