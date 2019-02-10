<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class ReproBugTest extends TestCase
{
    public function test(){
        // create a file of 6 bytes
        $path = __DIR__ . "/foo.txt";
        file_put_contents($path, "123456");
        // (1)
        // open that file for read and shared-lock it
        $file = fopen($path, "r");
        flock($file, LOCK_SH | LOCK_NB);
        // (2)
        // attempt to write that file concurrently while the lock is active
        var_dump(`php -r "var_dump(file_put_contents('$path', 'baz')); echo 123;"`);
        // this step fails as expected -- means the file is still "123456"
        // (3)
        // attempt to read the file's size
        clearstatcache(FALSE, $path);
        $fileSize = filesize($path);
        // should return 6 bytes but returns 0
        // (4)
        // this fails as consequence
        echo $fileSize . "\n";
        echo fread($file, $fileSize);
    }
}