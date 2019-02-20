<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use function Netmosfera\Opal\InternalTools\dirEmpty;
use PHPUnit\Framework\TestCase;

class dirEmptyTest extends TestCase
{
    public function test(){
        try{
            $f = [];

            $bd = __DIR__ . "/temp_dir";
            foreach(["a1", "a2", "a3"] as $a){
                foreach(["b1", "b2", "b3"] as $b){
                    foreach(["c1", "c2", "c3"] as $c){
                        mkdir($bd . "/" . $a . "/" . $b . "/" . $c, 0777, TRUE);
                        foreach(range(1, 3) as $i){
                            file_put_contents($f[] = $bd . "/$a/$b/$c/file$i.txt", "");
                        }
                    }
                    foreach(range(1, 3) as $i){
                        file_put_contents($f[] = $bd . "/$a/$b/file$i.txt", "");
                    }
                }
                file_put_contents($f[] = $bd . "/$a/file1.txt", "");
                foreach(range(1, 3) as $i){
                    file_put_contents($f[] = $bd . "/$a/file$i.txt", "");
                }
            }

            dirEmpty($bd);

            self:self::assertSame([], glob($bd . "/*"));

        }finally{
            foreach($f as $file){
                @unlink($file);
            }

            foreach(["a1", "a2", "a3"] as $a){
                foreach(["b1", "b2", "b3"] as $b){
                    foreach(["c1", "c2", "c3"] as $c){
                        @rmdir($bd . "/$a/$b/$c");
                    }
                    @rmdir($bd . "/$a/$b");
                }
                @rmdir($bd . "/$a");
            }
            @rmdir($bd);
        }
    }
}
