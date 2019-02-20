<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Loader;
use Netmosfera\Opal\PackageComponent;
use PHPUnit\Framework\TestCase;
use const DIRECTORY_SEPARATOR as DS;

class LoaderTest extends TestCase
{
    public function test_dynamic(){
        $sourceDirectoryPath = __DIR__ . DS . "source_dir";
        $compileDirectoryPath = __DIR__ . DS . "compile_dir";
        $lockPath = $compileDirectoryPath . DS . "lock";

        $saPath = $sourceDirectoryPath . DS . "Bar.php";
        $ssPath = $sourceDirectoryPath . DS . "qux.inc.php";
        $daPath = $compileDirectoryPath . DS . "A\\B\\Bar.php";
        $dsPath = $compileDirectoryPath . DS . "A\\B\\qux.inc.php";

        try{
            mkdir($sourceDirectoryPath, 0777, TRUE);
            mkdir($compileDirectoryPath, 0777, TRUE);
            file_put_contents($saPath, "<?php namespace A\\B; class Bar{} ");
            file_put_contents($ssPath, "<?php namespace A\\B; function qux(){ return 42; } ");

            $o = new Loader(FALSE);
            $o->addPackage("A", "B", $sourceDirectoryPath);
            $o->addPreprocessor(function(
                PackageComponent $component, Array $nodes
            ) use(&$calls): array{
                $calls[] = func_get_args();
                return $nodes;
            });
            $o->start($compileDirectoryPath);

            $aName = "A\\B\\Bar";
            self::assertInstanceOf("A\\B\\Bar", new $aName);

            $sName = "A\\B\\qux";
            self::assertSame(42, $sName());
        }finally{
            $o->__destruct();

            @unlink($saPath);
            @unlink($ssPath);
            @rmdir($sourceDirectoryPath);

            @unlink($daPath);
            @unlink($dsPath);
            @unlink($lockPath);
            @rmdir($compileDirectoryPath . DS . "A" . DS . "B");
            @rmdir($compileDirectoryPath . DS . "A");
            @rmdir($compileDirectoryPath);
        }
    }
}
