<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\InternalTools\File;

use PHPUnit\Framework\TestCase;
use const PHP_INT_MAX;
use function Netmosfera\Opal\InternalTools\File\fileRequire;
use function random_int;

class fileRequireTest extends TestCase
{
    public function test(){
        $path = __DIR__ . "/" . random_int(0, PHP_INT_MAX);
        file_put_contents($path, "<?php return get_defined_vars(); ");
        $actualVariables = fileRequire($path);
        $expectVariables = ["__OPAL_FILE__" => $path];
        self::assertSame($expectVariables, $actualVariables);
        unlink($path);
    }
}
