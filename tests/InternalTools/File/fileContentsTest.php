<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools\File;

use PHPUnit\Framework\TestCase;
use const PHP_INT_MAX;
use function Netmosfera\Opal\InternalTools\File\fileContents;
use function random_int;

class fileContentsTest extends TestCase
{
    public function test_can_read_while_shared_locked(){
        $file = random_int(0, PHP_INT_MAX) . ".txt";
        $path = __DIR__ . "/" . $file;
        file_put_contents($path, "42");
        $result = fileContents($path, 5.0, 0.5, function() use(&$file, &$otherResult){
            $path = __DIR__  . "/fileContents_readFileConcurrently.php";
            $otherResult = shell_exec('php "' . $path . '" ' . $file);
        });
        unlink($path);
        self::assertSame("TRUE\nFALSE\n", $otherResult);
        self::assertSame("42", $result);
    }

    public function test_can_not_write_while_shared_locked(){
        $file = random_int(0, PHP_INT_MAX) . ".txt";
        $path = __DIR__ . "/" . $file;
        file_put_contents($path, "42");
        $result = fileContents($path, 5.0, 0.5, function() use(&$file, &$otherResult){
            $path = __DIR__  . "/fileContents_writeFileConcurrently.php";
            $otherResult = shell_exec('php "' . $path . '" ' . $file);
        });
        unlink($path);
        self::assertSame("FALSE\nTRUE\n", $otherResult);
        self::assertSame("42", $result);
    }
}
