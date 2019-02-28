<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackagePath;
use Netmosfera\Opal\Path;
use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\InternalTools\componentFromPath;

class componentFromPathTest extends TestCase
{
    public function data2(){
        $dir = "c:\\aaa\\bbb";
        foreach(["", "\\prefix", "\\prefix\\prefix"] as $prefix){
            yield [$dir, $dir . $prefix . "\\1abc.php"];
            yield [$dir, $dir . $prefix . "\\abc-.php"];
            yield [$dir, $dir . $prefix . "\\-abc.php"];
            yield [$dir, $dir . $prefix . "\\àbc.php"];
            yield [$dir, $dir . $prefix . "\\cdè.php"];
            yield [$dir, $dir . $prefix . "\\.php"];
        }
    }

    /** @dataProvider data2 */
    public function test_is_null_if_invalid_identifiers(
        String $packagePathString, String $componentPathString
    ){
        $package = new Package("StarkIndustries", "IronManSuit");
        $packagePath = new PackagePath($package, new Path($packagePathString));
        $packageComponent = componentFromPath($packagePath, new Path($componentPathString));
        self::assertSame(NULL, $packageComponent);
    }

    public function data3(){
        $m = function(array $identifiers, $extension){
            $package = new Package("StarkIndustries", "IronManSuit");
            return new PackageComponent($package, $identifiers, $extension);
        };

        $prefix = "c:\\àèò";
        foreach(["", ".php", ".inc.php"] as $ext){
            yield [$prefix, $prefix . "\\abc" . $ext,  $m(["abc"], $ext)];
            yield [$prefix, $prefix . "\\abc\\foo" . $ext,  $m(["abc", "foo"], $ext)];
            yield [$prefix, $prefix . "/abc/foo" . $ext,  $m(["abc", "foo"], $ext)];
        }
    }

    /** @dataProvider data3 */
    public function test3(String $directory, String $file, PackageComponent $expected){
        // test that returns the PackageComponent object
        $package = new Package("StarkIndustries", "IronManSuit");
        $directory = new PackagePath($package, new Path($directory));
        self::assertEquals($expected, componentFromPath($directory, new Path($file)));
    }
}
