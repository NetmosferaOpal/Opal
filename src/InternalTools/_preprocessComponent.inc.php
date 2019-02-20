<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use Closure;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackageDirectory;
use PhpParser\ParserFactory as PF;
use PhpParser\PrettyPrinter\Standard;
use function umask;

function _preprocessComponent(
    PackageDirectory $directory,
    PackageComponent $component,
    Array $preprocessors,
    String $compileDirectory,
    Bool $executeIt,
    ?Int $directoryPermissions,
    ?Int $filePermissions
){
    $originFile = $directory->path . $component->relativeToPackagePath;

    $source = file_get_contents($originFile);
    if($preprocessors !== []){
        $nodes = (new PF())->create(PF::ONLY_PHP7)->parse($source);
        foreach($preprocessors as $preprocessor){
            assert($preprocessor instanceof Closure);
            $nodes = $preprocessor($component, $nodes);
        }
        $source = (new Standard())->prettyPrintFile($nodes);
    }

    $destinationFile = $compileDirectory . $component->absolutePath;

    $saveUMask = umask(0);
    @mkdir(dirname($destinationFile), $directoryPermissions ?? 0755, TRUE);
    umask($saveUMask);

    // @TODO umask file 0644
    file_put_contents($destinationFile, $source);
    if($executeIt) require $destinationFile; // @TODO clean scope
}
