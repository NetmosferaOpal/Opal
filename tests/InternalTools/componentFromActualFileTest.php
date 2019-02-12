<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use Exception;
use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackageDirectory;
use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\InternalTools\componentFromActualFile;

class componentFromActualFileTest extends TestCase
{
    function data1(){
        // different prefix
        yield ["/a", "/b/file.php"];
        yield ["/a", "/A/file.php"];
        yield ["/a/b/c", "/a/b/b/file.php"];
        yield ["/a/b/c", "/a/b/C/file.php"];

        // prefix is the same but it's not followed by a directory separator
        yield ["/Fi", "/File.php"];
        yield ["c:\\Path\\Dire", "c:\\Path\\Directory.php"];

        // prefix is the same but the directory separator is different
        yield ["c:/Path/To", "c:\\Path\\To/File.php"];
    }

    /** @dataProvider data1 */
    public function test1(String $directory, String $file){
        // test error if file does not belong to the provided directory
        $this->expectException(Exception::CLASS);
        $package = new Package("StarkIndustries", "IronManSuit");
        $directory = new PackageDirectory($package, $directory);
        componentFromActualFile($directory, $file);
    }

    function data2(){
        $dir = "c:\\aaa\\bbb\\";
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
    public function test2(String $directory, String $file){
        // test returns null if in-package identifiers are not valid php identifiers
        $package = new Package("StarkIndustries", "IronManSuit");
        $directory = new PackageDirectory($package, $directory);
        self::assertSame(NULL, componentFromActualFile($directory, $file));
    }

    function data3(){
        $m = function(array $identifiers, $extension){
            $package = new Package("StarkIndustries", "IronManSuit");
            return new PackageComponent($package, $identifiers, $extension);
        };

        $prefix = "c:\\àèò";
        foreach(["", ".php", ".inc.php"] as $ext){
            yield [$prefix, $prefix . "\\abc" . $ext,  $m(["abc"], $ext)];
            yield [$prefix, $prefix . "\\abc\\foo" . $ext,  $m(["abc", "foo"], $ext)];
            yield [$prefix, $prefix . "//abc//foo" . $ext,  $m(["abc", "foo"], $ext)];
        }
    }

    /** @dataProvider data3 */
    public function test3(String $directory, String $file, PackageComponent $expected){
        // test that returns the PackageComponent object
        $package = new Package("StarkIndustries", "IronManSuit");
        $directory = new PackageDirectory($package, $directory);
        self::assertEquals($expected, componentFromActualFile($directory, $file));
    }
}
