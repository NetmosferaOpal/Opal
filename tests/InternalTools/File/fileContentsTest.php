<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools\File;

use function exec;
use function file_get_contents;
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
        self::assertSame("foo", file_get_contents($filePath));

        $result = fileContents($filePath, 5.0, 0.5, function() use(
            &$fileName, &$out, &$return1, &$return2
        ){

        });

        var_dump(ini_get("disable_functions"));
        var_dump(ini_get("safe_mode"));
        var_dump(ini_get("safe_mode_exec_dir"));

        $shell_result = shell_exec(
            "php \"" . __DIR__ . "/fileContentsWriteFileConcurrently.php\" $fileName"
        );
        echo "\n\n\n";
        var_dump($shell_result);
        echo "\n\n\n";

        self::assertSame("foo", $result);
    }
}
