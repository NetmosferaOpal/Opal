<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\Loaders;

use Netmosfera\Opal\Path;
use Netmosfera\Opal\Loaders\StaticLoader;
use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackagePath;
use PHPUnit\Framework\TestCase;
use function ob_get_clean;
use function ob_start;

class StaticLoaderTest extends TestCase
{
    public function test(){
        try{
            $V = "V" . bin2hex(random_bytes(5));
            $P = "P" . bin2hex(random_bytes(5));

            $NS = "namespace $V\\$P;";
            $originPath = new Path("/origin-irrelevant");
            $targetPath = new Path(__DIR__ . "/target");
            mkdir("$targetPath/$V/$P", 0777, TRUE);

            file_put_contents("$targetPath/static-inclusions.php", "<?php echo 123;");
            file_put_contents("$targetPath/$V/$P/Foo.php", "<?php $NS class Foo{}");

            $package = new Package($V, $P);
            $packagePath = new PackagePath($package, $originPath);
            $packagePaths = [$package->id => $packagePath];
            $preprocessors = [];

            $loader = new StaticLoader();

            ob_start();
            $loader->start($packagePaths, $preprocessors, $targetPath, 0777, 0777);
            self::assertSame("123", ob_get_clean());

            $class = "$V\\$P\\Foo";
            self::assertInstanceOf($class, new $class);
        }finally{
            @unlink("$targetPath/$V/$P/Foo.php");
            @unlink("$targetPath/static-inclusions.php");
            @rmdir("$targetPath/$V/$P");
            @rmdir("$targetPath/$V");
            @rmdir("$targetPath");
        }
    }
}
