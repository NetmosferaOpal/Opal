<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools\File;

use PHPUnit\Framework\TestCase;
use const PHP_INT_MAX;
use function Netmosfera\Opal\InternalTools\File\fileRead;
use function Netmosfera\Opal\InternalTools\File\fileWrite;
use function random_int;

class fileReadWriteTest extends TestCase
{
    public function test_write_read(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        $r1 = fileWrite($path, "baz", 0755, 5.0, 0.5, function() use(&$path, &$r2){
            $r2 = fileRead($path, 1.0, 0.1);
        });
        unlink($path);
        self::assertNull($r2);
        self::assertTrue($r1);
    }

    public function test_write_write(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        $r1 = fileWrite($path, "baz", 0755, 5.0, 0.5, function() use(&$path, &$r2){
            $r2 = fileWrite($path, "bar", 0755, 1.0, 0.1);
        });
        unlink($path);
        self::assertFalse($r2);
        self::assertTrue($r1);
    }

    public function test_read_write(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        file_put_contents($path, "foo");
        $r1 = fileRead($path, 5.0, 0.5, function() use(&$path, &$r2){
            $r2 = fileWrite($path, "bar", 0755, 1.0, 0.1);
        });
        unlink($path);
        self::assertFalse($r2);
        self::assertSame("foo", $r1);
    }

    public function test_read_read(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        file_put_contents($path, "foo");
        $r1 = fileRead($path, 5.0, 0.5, function() use(&$path, &$r2){
            $r2 = fileRead($path, 5.0, 0.5);
        });
        unlink($path);
        self::assertSame("foo", $r2);
        self::assertSame("foo", $r1);
    }
}
