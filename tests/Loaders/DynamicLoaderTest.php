<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\Loaders;

use Netmosfera\Opal\Loaders\DynamicLoader;
use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackagePath;
use Netmosfera\Opal\Path;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Echo_;
use PHPUnit\Framework\TestCase;
use function ob_get_clean;
use function ob_start;

class DynamicLoaderTest extends TestCase
{
    public function test(){
        try{
            $V = "V" . bin2hex(random_bytes(5));
            $P = "P" . bin2hex(random_bytes(5));

            $NS = "namespace $V\\$P;";
            $originPath = new Path(__DIR__ . "/origin");
            $targetPath = new Path(__DIR__ . "/target");
            mkdir("$originPath", 0777, TRUE);

            file_put_contents("$originPath/staticFile.inc.php", "<?php $NS echo 123;");
            file_put_contents("$originPath/Foo.php", "<?php $NS class Foo{}");

            $package = new Package($V, $P);
            $packagePath = new PackagePath($package, $originPath);
            $packagePaths = [$package->id => $packagePath];
            $preprocessors = [function(PackageComponent $pc, Array $nodes): array{
                $nodes[] = new Echo_([new LNumber(456)]);
                return $nodes;
            }];

            $loader = new DynamicLoader();

            ob_start();
            $loader->start($packagePaths, $preprocessors, $targetPath, 0777, 0777);
            self::assertSame("123456", ob_get_clean());

            ob_start();
            $class = "$V\\$P\\Foo";
            self::assertInstanceOf($class, new $class);
            self::assertSame("456", ob_get_clean());

            self::assertTrue(file_exists("$targetPath/$V/$P/Foo.php"));
            self::assertTrue(file_exists("$targetPath/$V/$P/staticFile.inc.php"));

            $loader->__destruct();
        }finally{
            @unlink("$originPath/staticFile.inc.php");
            @unlink("$originPath/Foo.php");
            @rmdir("$originPath");
            @unlink("$targetPath/opal.lock");
            @rmdir("$targetPath");
        }
    }
}
