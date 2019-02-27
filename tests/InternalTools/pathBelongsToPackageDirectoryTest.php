<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageDirectory;
use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\InternalTools\pathBelongsToPackageDirectory;

class pathBelongsToPackageDirectoryTest extends TestCase
{
    public function data1(){
        // different prefix
        yield ["/a", "/b/file.php", FALSE];
        yield ["/a", "/A/file.php", FALSE];
        yield ["/a/b/c", "/a/b/b/file.php", FALSE];
        yield ["/a/b/c", "/a/b/C/file.php", FALSE];

        // prefix is the same but it's not followed by a directory separator
        yield ["/Fi", "/File.php", FALSE];
        yield ["c:\\Path\\Dire", "c:\\Path\\Directory.php", FALSE];

        // prefix is the same but the directory separator is different
        yield ["c:/Path/To", "c:\\Path\\To/File.php", FALSE];

        // same prefix
        yield ["c:/Path/To", "c:/Path/To/The/File.php", TRUE];
        yield ["c:/Path/To", "c:/Path/To\\The\\File.php", TRUE];
        yield ["/Path/To", "/Path/To/The/File.php", TRUE];
        yield ["/Path/To", "/Path/To\\The\\File.php", TRUE];
        yield ["", "/Path/To/The/File.php", TRUE];
        yield ["", "/Path/To\\The\\File.php", TRUE];
    }

    /** @dataProvider data1 */
    public function test1(String $directory, String $path, Bool $result){
        $package = new Package("StarkIndustries", "IronManSuit");
        $directory = new PackageDirectory($package, $directory);
        self::assertSame($result, pathBelongsToPackageDirectory($path, $directory));
    }
}
