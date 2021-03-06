<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\Files;

use Netmosfera\Opal\Path;
use PHPUnit\Framework\TestCase;
use const DIRECTORY_SEPARATOR as DS;
use const SORT_STRING;
use function Netmosfera\Opal\Files\readDirectory;
use function random_bytes;
use function rmdir;

// @TODO cleanup this a bit

class readDirectoryTest extends TestCase
{
    public function test_lots_of_files(){
        try{
            $bd = __DIR__ . DS . "temp_directory";

            $countOfFiles = -1;
            $fileNames = function() use(&$countOfFiles){
                if($countOfFiles++ > 4) $countOfFiles = 0;
                if($countOfFiles === 0) return [];
                $fileNames = [];
                foreach(range(1, $countOfFiles) as $_){
                    $fileNames[] = "f" . bin2hex(random_bytes(5)) . ".txt";
                }
                return $fileNames;
            };

            foreach(range(1, 4) as $a){
                foreach(range(1, 4) as $b){
                    foreach(range(1, 4) as $c){
                        mkdir($bd . DS . "a$a" . DS . "b$b" . DS . "c$c", 0777, TRUE);
                    }
                }
            }

            $files = [];

            foreach(range(1, 4) as $a){
                foreach($fileNames() as $fileName){
                    $file = $files[] = $bd . DS . "a$a" . DS . $fileName;
                    file_put_contents($file, "");
                }
                foreach(range(1, 4) as $b){
                    foreach($fileNames() as $fileName){
                        $file = $files[] = $bd . DS . "a$a" . DS . "b$b" . $fileName;
                        file_put_contents($file, "");
                    }
                    foreach(range(1, 4) as $c){
                        foreach($fileNames() as $fileName){
                            $file = $files[] = $bd . DS . "a$a" . DS . "b$b" . DS . "c$c" . $fileName;
                            file_put_contents($file, "");
                        }
                    }
                }
            }

            $actualFiles = readDirectory(new Path($bd));
            foreach($actualFiles as $actualFile){
                assert($actualFile instanceof Path);
                $actualPaths[] = $actualFile->string;
            }

            sort($files, SORT_STRING);
            sort($actualPaths, SORT_STRING);

            self::assertSame($files, $actualPaths);

        }finally{

            foreach($files as $file){
                unlink($file);
            }

            foreach(range(1, 4) as $a){
                foreach(range(1, 4) as $b){
                    foreach(range(1, 4) as $c){
                        rmdir($bd . DS . "a$a" . DS . "b$b" . DS . "c$c");
                    }
                    rmdir($bd . DS . "a$a" . DS . "b$b");
                }
                rmdir($bd . DS . "a$a");
            }
            rmdir($bd);
        }

    }
}
