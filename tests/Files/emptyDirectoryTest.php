<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\Files;

use Netmosfera\Opal\Path;
use PHPUnit\Framework\TestCase;
use function Netmosfera\Opal\Files\emptyDirectory;

class emptyDirectoryTest extends TestCase
{
    public function test(){
        try{
            $files = [];
            $directories = [];

            $baseDirectory = __DIR__ . "/temp";

            foreach(["a1", "a2", "a3"] as $a){
                foreach(["b1", "b2", "b3"] as $b){
                    foreach(["c1", "c2", "c3"] as $c){
                        $directories[] = "$baseDirectory/$a/$b/$c";
                        mkdir(end($directories), 0777, TRUE);
                        foreach(range(1, 3) as $i){
                            $files[] = "$baseDirectory/$a/$b/$c/file_$i.txt";
                            file_put_contents(end($files), "");
                        }
                    }
                    $directories[] = "$baseDirectory/$a/$b";
                    foreach(range(1, 3) as $i){
                        $files[] = "$baseDirectory/$a/$b/file_$i.txt";
                        file_put_contents(end($files), "");
                    }
                }
                $directories[] = "$baseDirectory/$a";
                foreach(range(1, 3) as $i){
                    $files[] = "$baseDirectory/$a/file_$i.txt";
                    file_put_contents(end($files), "");
                }
            }
            $directories[] = "$baseDirectory";

            emptyDirectory(new Path($baseDirectory));

            self:self::assertSame([], glob($baseDirectory . "/*"));

        }finally{
            foreach($files as $file){
                @unlink($file);
            }

            foreach($directories as $directory){
                @rmdir($directory);
            }
        }
    }
}
