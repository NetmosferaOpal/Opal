<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\Files;

use Error;
use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\Files\lockDirectory;

class lockDirectoryTest extends TestCase
{
    public function test_acquires_lock(){
        try{
            $handle = lockDirectory(__DIR__ . "/foo", 0777, 30);
            self::assertTrue(file_exists(__DIR__ . "/foo/opal.lock"));
            self::assertTrue(is_resource($handle));
        }finally{
            @fclose($handle);
            @unlink(__DIR__ . "/foo/opal.lock");
            @rmdir(__DIR__ . "/foo");
        }
    }

    public function test_denies_concurrent_lock(){
        try{
            $this->expectException(Error::CLASS);
            $handle = lockDirectory(__DIR__ . "/foo", 0777, 30);
            lockDirectory(__DIR__ . "/foo", 0777, 1);
        }finally{
            @fclose($handle);
            @unlink(__DIR__ . "/foo/opal.lock");
            @rmdir(__DIR__ . "/foo");
        }
    }

    public function test_directory_is_not_empty(){
        try{
            $this->expectException(Error::CLASS);
            mkdir(__DIR__ . "/foo");
            file_put_contents(__DIR__ . "/foo/baz.txt", "");
            lockDirectory(__DIR__ . "/foo", 0777, 1);
        }finally{
            @unlink(__DIR__ . "/foo/opal.lock");
            @unlink(__DIR__ . "/foo/baz.txt");
            @rmdir(__DIR__ . "/foo");
        }
    }
}
