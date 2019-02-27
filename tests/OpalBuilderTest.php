<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Loader;
use Netmosfera\Opal\OpalBuilder;
use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackagePath;
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

        $path1 = new PackagePath(new Package("A", "B"), "/path1");
        $path2 = new PackagePath(new Package("C", "D"), "/path2");
        $path3 = new PackagePath(new Package("E", "FD"), "/path3");
        $paths = [$path1, $path2, $path3];
        /** @var PackagePath[] $paths */

        $loader = new class() implements Loader{
            public $result;

            public function start(
                Array $directories,
                Array $preprocessors,
                String $compileDirectory,
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

        $builder->start("/a", 0777, 0777);

        $expect = [$indexedPaths, $preprocessors, "/a", 0777, 0777];
        self::assertSame($expect, $loader->result);
    }
}
