<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use Netmosfera\Opal\Identifier;
use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackagePath;
use Netmosfera\Opal\Path;
use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\InternalTools\componentFromPath;

class componentFromPathTest extends TestCase
{
    public function data2(){
        $invalidIdentifiers[] = ""; // empty
        $invalidIdentifiers[] = "1_starts_with_number";
        $invalidIdentifiers[] = "-_contains_dash";
        $invalidIdentifiers[] = "contains_-_dash";
        $invalidIdentifiers[] = "contains_dash_-";
        $invalidIdentifiers[] = "çontains_non_ascii";
        $invalidIdentifiers[] = "contains_non_asciì";

        foreach(["\\", "/"] as $s){
            $packagePath[] = "c:";
            $packagePath[] = "c:{$s}fòo";
            $packagePath[] = "c:{$s}foo{$s}bàr";
            $packagePath[] = "";
            $packagePath[] = "{$s}foo";
            $packagePath[] = "{$s}foo{$s}bar";

            $pathToComponent[] = "{$s}foo";
            $pathToComponent[] = "{$s}foo{$s}bar";
            $pathToComponent[] = "{$s}foo{$s}bar{$s}baz";

            foreach($packagePath as $pp){
                foreach($pathToComponent as $pc){
                    foreach($invalidIdentifiers as $ii){
                        yield [$pp, "{$pp}{$pc}{$s}$ii.php"];
                    }
                }
            }
        }
    }

    /** @dataProvider data2 */
    public function test_is_null_if_invalid_identifiers(
        String $packagePathStr, String $componentPathStr
    ){
        $package = new Package(new Identifier("A"), new Identifier("B"));
        $packagePath = new PackagePath($package, new Path($packagePathStr));
        $packageComponent = componentFromPath($packagePath, new Path($componentPathStr));
        self::assertSame(NULL, $packageComponent);
    }

    public function test_object_creation(){
        $package = new Package(new Identifier("A"), new Identifier("B"));
        $directory = new PackagePath($package, new Path("/path/to/src"));
        $actual = componentFromPath($directory, new Path("/path/to/src/Baz/Quz.inc.php"));
        self::assertSame($package, $actual->package);
        self::assertSame("/A/B/Baz/Quz.inc.php", $actual->absolutePath);
        self::assertSame("/Baz/Quz.inc.php", $actual->relativeToPackagePath);
        self::assertSame("/Baz/Quz", $actual->name->path());
        self::assertSame(".inc.php", $actual->extensions);
    }
}
