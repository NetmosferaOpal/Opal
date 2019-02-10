<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools\File;

use function exec;
use function Netmosfera\Opal\InternalTools\File\fileContents;
use const PHP_INT_MAX;
use PHPUnit\Framework\TestCase;
use function random_int;

class fileContentsTest extends TestCase
{
    public function test_can_not_write_while_shared_locked(){
        $fileName = random_int(0, PHP_INT_MAX) . ".txt";
        $filePath = __DIR__ . "/" . $fileName;
        file_put_contents($filePath, "foo");
        fileContents($filePath, 5.0, 0.5, function() use(&$fileName){
            $result = `start php fileContentsWriteFileConcurrently.php $fileName`;
            echo $result;
        });
    }
}
