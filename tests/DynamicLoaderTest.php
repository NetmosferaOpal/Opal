<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\DynamicLoader;
use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackagePath;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Echo_;
use PHPUnit\Framework\TestCase;
use function ob_get_clean;
use function ob_start;

class DynamicLoaderTest extends TestCase
{
    public function test(){
        try{
            $vendorName = "V" . bin2hex(random_bytes(5));
            $packageName = "P" . bin2hex(random_bytes(5));

            mkdir(__DIR__ . "/origin", 0777, TRUE);

            file_put_contents(
                __DIR__ . "/origin/staticFile.inc.php",
                "<?php namespace $vendorName\\$packageName; echo 123;"
            );

            file_put_contents(
                __DIR__ . "/origin/Foo.php",
                "<?php namespace $vendorName\\$packageName; class Foo{}"
            );

            $preprocessors = [function(PackageComponent $pc, Array $nodes): array{
                $nodes[] = new Echo_([new LNumber(456)]);
                return $nodes;
            }];

            $package = new Package($vendorName, $packageName);
            $directory = new PackagePath($package, __DIR__ . "/origin");
            $directories = [$package->id => $directory];
            $compileDirectory = __DIR__ . "/destination";
            $loader = new DynamicLoader();

            ob_start();
            $loader->start($directories, $preprocessors, $compileDirectory, 0777, 0777);
            self::assertSame("123456", ob_get_clean());

            ob_start();
            $class = "$vendorName\\$packageName\\Foo";
            self::assertInstanceOf($class, new $class);
            self::assertSame("456", ob_get_clean());

            self::assertTrue(file_exists(
                __DIR__ . "/destination/$vendorName/$packageName/Foo.php"
            ));

            self::assertTrue(file_exists(
                __DIR__ . "/destination/$vendorName/$packageName/staticFile.inc.php"
            ));

            $loader->__destruct();
        }finally{
            @unlink(__DIR__ . "/origin/staticFile.inc.php");
            @unlink(__DIR__ . "/origin/Foo.php");
            @rmdir(__DIR__ . "/origin");
            @unlink(__DIR__ . "/destination/opal.lock");
            @rmdir(__DIR__ . "/destination");
        }
    }
}
