<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools\File;

use PHPUnit\Framework\TestCase;
use const PHP_INT_MAX;
use function Netmosfera\Opal\InternalTools\File\fileRead;
use function Netmosfera\Opal\InternalTools\File\fileWrite;
use function random_int;

class fileReadWriteTest extends TestCase
{
    public function test_lock_write_while_read(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        file_put_contents($path, "foo");
        $r1 = fileRead($path, 5.0, 0.5, NULL, NULL, function() use(&$path, &$r2){
            $r2 = fileWrite($path, "bar", 0755, 1.0, 0.1);
        });
        unlink($path);
        self::assertSame("foo", $r1);
        self::assertFalse($r2);
    }

    public function test_lock_read_while_read(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        file_put_contents($path, "foo");
        $r1 = fileRead($path, 5.0, 0.5, NULL, NULL, function() use(&$path, &$r2){
            $r2 = fileRead($path, 5.0, 0.5);
        });
        unlink($path);
        self::assertSame("foo", $r1);
        self::assertSame("foo", $r2);
    }

    public function test_lock_read_while_write(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        $r1 = fileWrite($path, "baz", 0755, 5.0, 0.5, function() use(&$path, &$r2){
            $r2 = fileRead($path, 1.0, 0.1);
        });
        unlink($path);
        self::assertTrue($r1);
        self::assertNull($r2);
    }

    public function test_lock_write_while_write(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        $r1 = fileWrite($path, "baz", 0755, 5.0, 0.5, function() use(&$path, &$r2){
            $r2 = fileWrite($path, "bar", 0755, 1.0, 0.1);
        });
        unlink($path);
        self::assertTrue($r1);
        self::assertFalse($r2);
    }

    //[][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][]

    public function test_read_nonexistent_file(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        $r1 = fileRead($path, 1.0, 0.1);
        self::assertSame(NULL, $r1);
    }

    public function test_read_initially_nonexistent_file(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        $attempts = 0;
        $r = fileRead($path, 5.0, 0.1, function(Bool $success) use(&$path, &$attempts){
            $attempts++;
            if($attempts <= 4) self::assertFalse($success);
            if($attempts === 4) file_put_contents($path, "foo");
            if($attempts === 5) self::assertTrue($success);
        });
        unlink($path);
        self::assertSame("foo", $r);
    }

    public function test_read_initially_locked_file(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        $handle = fopen($path, "c");
        flock($handle, LOCK_EX | LOCK_NB);
        $attempts = 0;
        $r = fileRead($path, 5.0, 0.1, NULL, function(Bool $lockAcquired) use(
            &$attempts, &$handle
        ){
            $attempts++;
            if($attempts <= 3) self::assertFalse($lockAcquired);
            if($attempts === 3){ flock($handle, LOCK_UN); fclose($handle); }
            if($attempts === 4) self::assertTrue($lockAcquired);
        });
        unlink($path);
        self::assertSame("foo", $r);
    }
}
