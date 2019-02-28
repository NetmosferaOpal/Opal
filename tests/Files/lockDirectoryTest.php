<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\Files;

use Error;
use Netmosfera\Opal\Path;
use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\Files\lockDirectory;

class lockDirectoryTest extends TestCase
{
    public function test_acquires_lock(){
        try{
            $handle = lockDirectory(new Path(__DIR__ . "/temp"), 0777, 30);
            self::assertTrue(file_exists(__DIR__ . "/temp/opal.lock"));
            self::assertTrue(is_resource($handle));
        }finally{
            @fclose($handle);
            @unlink(__DIR__ . "/temp/opal.lock");
            @rmdir(__DIR__ . "/temp");
        }
    }

    public function test_denies_concurrent_lock(){
        try{
            $this->expectException(Error::CLASS);
            $handle = lockDirectory(new Path(__DIR__ . "/temp"), 0777, 30);
            lockDirectory(new Path(__DIR__ . "/temp"), 0777, 1);
        }finally{
            @fclose($handle);
            @unlink(__DIR__ . "/temp/opal.lock");
            @rmdir(__DIR__ . "/temp");
        }
    }

    public function test_directory_is_not_empty(){
        try{
            $this->expectException(Error::CLASS);
            mkdir(__DIR__ . "/temp");
            file_put_contents(__DIR__ . "/temp/baz.txt", "");
            lockDirectory(new Path(__DIR__ . "/temp"), 0777, 1);
        }finally{
            @unlink(__DIR__ . "/temp/opal.lock");
            @unlink(__DIR__ . "/temp/baz.txt");
            @rmdir(__DIR__ . "/temp");
        }
    }
}
