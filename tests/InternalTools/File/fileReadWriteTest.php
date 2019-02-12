<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools\File;

use PHPUnit\Framework\TestCase;
use const LOCK_SH;
use const PHP_INT_MAX;
use function file_put_contents;
use function fwrite;
use function Netmosfera\Opal\InternalTools\File\fileRead;
use function Netmosfera\Opal\InternalTools\File\fileWrite;
use function random_int;

class fileReadWriteTest extends TestCase
{
    public function test_prevents_write_lock_while_reading(){
        $path = $this->samplePath("foo");
        $contents = fileRead($path, 5.0, 0.5, NULL, function() use(
            &$path, &$written
        ){
            $written = fileWrite($path, "bar", 0755, 1.0, 0.1);
        });
        self::assertSame("foo", $contents);
        self::assertFalse($written);
    }

    public function test_does_not_prevent_read_lock_while_reading(){
        $path = $this->samplePath("foo");
        $contents1 = fileRead($path, 5.0, 0.5, NULL, function() use(
            &$path, &$contents2
        ){
            $contents2 = fileRead($path, 5.0, 0.5);
        });
        self::assertSame("foo", $contents1);
        self::assertSame("foo", $contents2);
    }

    public function test_prevents_read_lock_while_writing(){
        $path = $this->samplePath();
        $written = fileWrite($path, "baz", 0755, 5.0, 0.5, NULL, NULL, function() use(
            &$path, &$contents
        ){
            $contents = fileRead($path, 1.0, 0.1);
        });
        self::assertTrue($written);
        self::assertNull($contents);
    }

    public function test_prevents_write_lock_while_writing(){
        $path = $this->samplePath();
        $written1 = fileWrite($path, "baz", 0755, 5.0, 0.5, NULL, NULL, function() use(
            &$path, &$written2
        ){
            $written2 = fileWrite($path, "bar", 0755, 1.0, 0.1);
        });
        self::assertTrue($written1);
        self::assertFalse($written2);
    }

    //[][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][]

    public function test_read_empty_file(){
        $path = $this->samplePath("");
        $contents = fileRead($path, 1.0, 0.1);
        self::assertSame("", $contents);
    }

    public function test_read_nonexistent_file(){
        $path = $this->samplePath();
        $contents = fileRead($path, 1.0, 0.1);
        self::assertSame(NULL, $contents);
    }

    public function test_read_initially_nonexistent_file(){
        $path = $this->samplePath();
        $contents = fileRead($path, 5.0, 0.1, function(Bool $success) use(
            &$path, &$attempts
        ){
            $attempts = ($attempts ?? 0) + 1;
            if($attempts <= 3) self::assertFalse($success);
            if($attempts === 3) file_put_contents($path, "foo");
            if($attempts === 4) self::assertTrue($success);
            self::assertLessThan(5, $attempts);
        });
        self::assertSame("foo", $contents);
    }

    //[][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][]

    public function data_read_waits_for_write_to_finish(){
        yield [""];
        yield ["some data"];
    }

    /** @dataProvider data_read_waits_for_write_to_finish */
    public function test_read_waits_for_write_to_finish(String $data){
        $path = $this->samplePath();
        $handle = fopen($path, "c");
        flock($handle, LOCK_EX | LOCK_NB);
        fwrite($handle, $data);
        $afterLock = function(Bool $lockAcquired) use(&$attempts, &$handle){
            $attempts = ($attempts ?? 0) + 1;
            if($attempts <= 3) self::assertFalse($lockAcquired);
            if($attempts === 3){ flock($handle, LOCK_UN); fclose($handle); }
            if($attempts === 4) self::assertTrue($lockAcquired);
            self::assertLessThan(5, $attempts);
        };
        $actualData = fileRead($path, 5.0, 0.1, NULL, $afterLock);
        self::assertSame($data, $actualData);
    }

    public function data_write_waits_for_write_to_finish(){
        $data = ["", "some data"];
        foreach($data as $v1){
            foreach($data as $v2){
                yield [$v1, $v2];
            }
        }
    }

    /** @dataProvider data_write_waits_for_write_to_finish */
    public function test_write_waits_for_write_to_finish(String $data1, String $data2){
        $path = $this->samplePath();
        $handle = fopen($path, "c");
        flock($handle, LOCK_EX | LOCK_NB);
        fwrite($handle, $data1);
        $afterLock = function(Bool $lockAcquired) use(&$attempts, &$handle){
            $attempts = ($attempts ?? 0) + 1;
            if($attempts <= 3) self::assertFalse($lockAcquired);
            if($attempts === 3){ flock($handle, LOCK_UN); fclose($handle); }
            if($attempts === 4) self::assertTrue($lockAcquired);
            self::assertLessThan(5, $attempts);
        };
        $written = fileWrite($path, $data2, 0755, 5.0, 0.1, NULL, NULL, $afterLock);
        self::assertTrue($written);
        self::assertSame($data2, file_get_contents($path));
    }

    public function test_write_waits_for_read_to_finish(){
        $path = $this->samplePath("bar");
        $handle = fopen($path, "r");
        flock($handle, LOCK_SH | LOCK_NB);
        $afterLock = function(Bool $lockAcquired) use(&$attempts, &$handle){
            $attempts = ($attempts ?? 0) + 1;
            if($attempts <= 3) self::assertFalse($lockAcquired);
            if($attempts === 3){ flock($handle, LOCK_UN); fclose($handle); }
            if($attempts === 4) self::assertTrue($lockAcquired);
            self::assertLessThan(5, $attempts);
        };
        $written = fileWrite($path, "foo", 777, 5.0, 0.1, NULL, NULL, $afterLock);
        self::assertTrue($written);
    }

    public function test_read_does_not_wait_for_read_to_finish(){
        $path = $this->samplePath("bar");
        $handle = fopen($path, "r");
        flock($handle, LOCK_SH | LOCK_NB);
        $afterLock = function(Bool $lockAcquired) use(&$attempts, &$handle){
            $attempts = ($attempts ?? 0) + 1;
            self::assertTrue($lockAcquired);
            self::assertSame(1, $attempts);
            flock($handle, LOCK_UN);
            fclose($handle);
        };
        $contents = fileRead($path, 5.0, 0.1, NULL, $afterLock);
        self::assertSame("bar", $contents);
    }

    //[][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][]

    private $paths;

    private function samplePath(?String $createFileAndSetContentsTo = NULL){
        $this->paths = $this->paths ?? [];
        $this->paths[] = $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX) . ".txt";
        if($createFileAndSetContentsTo !== NULL){
            file_put_contents($path, $createFileAndSetContentsTo);
        }
        return $path;
    }

    public function tearDown(): void{
        parent::tearDown();
        foreach($this->paths as $path){
            @unlink($path);
        }
    }
}
