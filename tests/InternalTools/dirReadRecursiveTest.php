<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools;

use PHPUnit\Framework\TestCase;
use const DIRECTORY_SEPARATOR as DS;
use const SORT_STRING;
use function base64_encode;
use function Netmosfera\Opal\InternalTools\dirReadRecursive;
use function random_bytes;
use function random_int;
use function rmdir;

class dirReadRecursiveTest extends TestCase
{
    public function test_lots_of_files(){
        $bd = __DIR__ . DS . "temp_directory";

        $countOfFiles = -1;
        $fileNames = function() use(&$countOfFiles){
            if($countOfFiles++ > 4) $countOfFiles = 0;
            if($countOfFiles === 0) return [];
            $fileNames = [];
            foreach(range(1, $countOfFiles) as $_){
                $name = base64_encode(random_bytes(random_int(1, 30)));
                $nameClean = preg_replace("/[^a-zA-Z0-9]+/", "_", $name);
                $fileNames[] = $nameClean . ".txt";
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

        $actualFiles = dirReadRecursive($bd);

        sort($files, SORT_STRING);
        sort($actualFiles, SORT_STRING);

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

        self::assertSame($files, $actualFiles);
    }
}
