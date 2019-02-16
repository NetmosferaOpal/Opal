<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools\File;

use function file_put_contents;
use const DIRECTORY_SEPARATOR as DS;
use function Netmosfera\Opal\InternalTools\File\dirRead;
use PHPUnit\Framework\TestCase;
use const SORT_STRING;

class dirReadTest extends TestCase
{
    public function test_directory_is_deleted_while_reading(){
        try{
            $d = __DIR__ . DS . "popo" . DS;

            mkdir($d);
            file_put_contents($d . "a.txt", "");
            file_put_contents($d . "b.txt", "");
            file_put_contents($d . "c.txt", "");

            $count = 0;
            $actual = dirRead($d, NULL, function($path) use($d, &$count, &$expect){
                $count++;
                if($count == 1){
                    unlink($d . "a.txt");
                    unlink($d . "b.txt");
                    unlink($d . "c.txt");
                    rmdir($d);
                }
            });

            $expect = [];
            $expect[] = $d . "a.txt";
            $expect[] = $d . "b.txt";
            $expect[] = $d . "c.txt";
            sort($actual, SORT_STRING);
            self::assertSame($expect, $actual);
        }finally{
            @unlink($d . "a.txt");
            @unlink($d . "b.txt");
            @unlink($d . "c.txt");
            @rmdir($d);
        }
    }

    public function test_directory_is_deleted_after_open(){
        try{
            $d = __DIR__ . DS . "popo" . DS;

            mkdir($d);
            file_put_contents($d . "a.txt", "");
            file_put_contents($d . "b.txt", "");
            file_put_contents($d . "c.txt", "");

            $actual = dirRead($d, function() use($d){
                unlink($d . "a.txt");
                unlink($d . "b.txt");
                unlink($d . "c.txt");
                rmdir($d);
            });

            self::assertSame([], $actual);
        }finally{
            @unlink($d . "a.txt");
            @unlink($d . "b.txt");
            @unlink($d . "c.txt");
            @rmdir($d);
        }
    }

    public function test_directory_is_emptied_after_open(){
        try{
            $d = __DIR__ . DS . "popo" . DS;

            mkdir($d);
            file_put_contents($d . "a.txt", "");
            file_put_contents($d . "b.txt", "");
            file_put_contents($d . "c.txt", "");

            $actual = dirRead($d, function() use($d){
                unlink($d . "a.txt");
                unlink($d . "b.txt");
                unlink($d . "c.txt");
            });

            self::assertSame([], $actual);
        }finally{
            @unlink($d . "a.txt");
            @unlink($d . "b.txt");
            @unlink($d . "c.txt");
            @rmdir($d);
        }
    }

    public function test(){
        try{
            $d = __DIR__ . DS . "popo" . DS;
            mkdir($d);
            file_put_contents($d . "a.txt", "");
            file_put_contents($d . "b.txt", "");
            file_put_contents($d . "c.txt", "");

            $actual = dirRead($d);

            sort($actual);

            $expect[] = $d . "a.txt";
            $expect[] = $d . "b.txt";
            $expect[] = $d . "c.txt";
            self::assertSame($expect, $actual);
        }finally{
            unlink($d . "a.txt");
            unlink($d . "b.txt");
            unlink($d . "c.txt");
            rmdir($d);
        }
    }
}
