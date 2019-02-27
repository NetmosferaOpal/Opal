<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use function Netmosfera\Opal\InternalTools\lockDirectory;
use Netmosfera\Opal\InternalTools\LockTimeout;
use Netmosfera\Opal\InternalTools\NonEmptyDirectory;
use PHPUnit\Framework\TestCase;

class lockDirectoryTest extends TestCase
{
    public function test_acquires_lock(){
        try{
            $handle = lockDirectory(__DIR__ . "/foo", 0777, 30);
            self::assertTrue(file_exists(__DIR__ . "/foo/opal.lock"));
            self::assertTrue(is_resource($handle));
            fclose($handle);
        }finally{
            @unlink(__DIR__ . "/foo/opal.lock");
            @rmdir(__DIR__ . "/foo");
        }
    }

    public function test_denies_concurrent_lock(){
        try{
            $handle = lockDirectory(__DIR__ . "/foo", 0777, 30);
            self::assertInstanceOf(LockTimeout::CLASS, lockDirectory(__DIR__ . "/foo", 0777, 1));
            fclose($handle);
        }finally{
            @unlink(__DIR__ . "/foo/opal.lock");
            @rmdir(__DIR__ . "/foo");
        }
    }

    public function test_directory_is_not_empty(){
        try{
            mkdir(__DIR__ . "/foo");
            file_put_contents(__DIR__ . "/foo/baz.txt", "");
            self::assertInstanceOf(NonEmptyDirectory::CLASS, lockDirectory(__DIR__ . "/foo", 0777, 1));
        }finally{
            @unlink(__DIR__ . "/foo/opal.lock");
            @unlink(__DIR__ . "/foo/baz.txt");
            @rmdir(__DIR__ . "/foo");
        }
    }
}
