<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Identifier;
use Netmosfera\Opal\Loaders\Loader;
use Netmosfera\Opal\OpalBuilder;
use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackagePath;
use Netmosfera\Opal\Path;
use PHPUnit\Framework\TestCase;

class OpalBuilderTest extends TestCase
{
    public function test(){

        $p1 = function(PackageComponent $component, Array $nodes): array{};
        $p2 = function(PackageComponent $component, Array $nodes): array{};
        $p3 = function(PackageComponent $component, Array $nodes): array{};
        $preprocessors[spl_object_id($p1)] = $p1;
        $preprocessors[spl_object_id($p2)] = $p2;
        $preprocessors[spl_object_id($p3)] = $p3;

        $i = function(String $identifier){ return new Identifier($identifier); };

        $path1 = new PackagePath(new Package($i("A"), $i("B")), new Path("/path1"));
        $path2 = new PackagePath(new Package($i("C"), $i("D")), new Path("/path2"));
        $path3 = new PackagePath(new Package($i("E"), $i("F")), new Path("/path3"));
        $paths = [$path1, $path2, $path3];
        /** @var PackagePath[] $paths */

        $loader = new class() implements Loader{
            public $result;

            public function start(
                Array $directories,
                Array $preprocessors,
                Path $compileDirectory,
                Int $compileDirectoryPermissions,
                Int $compileFilePermissions
            ){
                $this->result = func_get_args();
            }
        };

        $builder = new OpalBuilder($loader);

        foreach($preprocessors as $preprocessor){
            $builder->addPreprocessor($preprocessor);
        }

        $indexedPaths = [];
        foreach($paths as $path){
            $indexedPaths[$path->package->id] = $path;
            $builder->addPackage($path);
        }

        $compileDirectory = new Path("/a");
        $builder->start($compileDirectory, 0777, 0777);

        $expect = [$indexedPaths, $preprocessors, $compileDirectory, 0777, 0777];
        self::assertSame($expect, $loader->result);
    }
}
