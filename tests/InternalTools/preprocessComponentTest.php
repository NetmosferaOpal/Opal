<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackagePath;
use Netmosfera\Opal\Path;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Echo_;
use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\InternalTools\preprocessComponent;
use function ob_get_clean;
use function ob_start;

class preprocessComponentTest extends TestCase
{
    public function data(){
        yield [TRUE];
        yield [FALSE];
    }

    /** @dataProvider data */
    public function test(Bool $executeIt){
        try{
            $p = new Package("StarkIndustries", "HulkBuster");
            $d = new PackagePath($p, new Path(__DIR__ . "/origin"));
            $c = new PackageComponent($p, ["Foo", "Bar", "Baz"], ".php");
            $compileDirectoryPath = new Path(__DIR__ . "/destination");
            $originPath = $d->path->path . $c->relativeToPackagePath;
            $destinationPath = $compileDirectoryPath->path . $c->absolutePath;

            mkdir(dirname($originPath), 0777, TRUE);
            file_put_contents($originPath, "<?php echo 123;");

            $preprocessors = [function(PackageComponent $c, Array $nodes): array{
                $nodes[] = new Echo_([new LNumber(456)]);
                return $nodes;
            }];

            ob_start();
            preprocessComponent(
                $d, $c, $preprocessors, $compileDirectoryPath,
                $executeIt, 0777, 0644
            );
            $output = ob_get_clean();

            self::assertTrue(file_exists($destinationPath));
            if($executeIt) self::assertSame("123456", $output);
            // @TODO test file permissions
        }finally{
            @unlink($originPath);
            @rmdir(dirname($originPath));
            @rmdir(dirname(dirname($originPath)));
            @rmdir(dirname(dirname(dirname($originPath))));
            @unlink($destinationPath);
            @rmdir(dirname($destinationPath));
            @rmdir(dirname(dirname($destinationPath)));
            @rmdir(dirname(dirname(dirname($destinationPath))));
            @rmdir(dirname(dirname(dirname(dirname($destinationPath)))));
            @rmdir(dirname(dirname(dirname(dirname(dirname($destinationPath))))));
        }
    }
}
