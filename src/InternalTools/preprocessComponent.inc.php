<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use Closure;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackagePath;
use PhpParser\ParserFactory as PF;
use PhpParser\PrettyPrinter\Standard;
use function chmod;
use function Netmosfera\Opal\Files\isNormalizedPath;
use function umask;

function preprocessComponent(
    PackagePath $packagePath,
    PackageComponent $packageComponent,
    Array $preprocessors,
    String $compileDirectory,
    Bool $executeIt,
    ?Int $directoryPermissions,
    ?Int $filePermissions
){
    assert(isNormalizedPath($compileDirectory));

    $originFile = $packagePath->path . $packageComponent->relativeToPackagePath;

    $source = file_get_contents($originFile);
    if($preprocessors !== []){
        $nodes = (new PF())->create(PF::ONLY_PHP7)->parse($source);
        foreach($preprocessors as $preprocessor){
            assert($preprocessor instanceof Closure);
            $nodes = $preprocessor($packageComponent, $nodes);
        }
        $source = (new Standard())->prettyPrintFile($nodes);
    }

    $destinationFile = $compileDirectory . $packageComponent->absolutePath;

    $saveUMask = umask(0);

    @mkdir(dirname($destinationFile), $directoryPermissions ?? 0755, TRUE);

    file_put_contents($destinationFile, $source);
    chmod($destinationFile, $filePermissions ?? 0644);

    if($executeIt){
        (function($__OPAL_FILE__){
            require $__OPAL_FILE__;
        })($destinationFile);
    }

    umask($saveUMask);
}
